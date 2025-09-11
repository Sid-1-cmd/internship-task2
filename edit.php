<?php
session_start();
require 'db.php';

// ----------------------------
// Require login
// ----------------------------
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// ----------------------------
// Validate post ID
// ----------------------------
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['message'] = "⚠️ Invalid post ID.";
    header("Location: index.php");
    exit();
}

// ----------------------------
// Fetch post securely
// ----------------------------
$stmt = $conn->prepare("SELECT id, title, content, user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    $_SESSION['message'] = "⚠️ Post not found.";
    header("Location: index.php");
    exit();
}

// ----------------------------
// Check permissions
// ----------------------------
if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $post['user_id']) {
    $_SESSION['message'] = "⛔ You do not have permission to edit this post.";
    header("Location: index.php");
    exit();
}

// ----------------------------
// CSRF Protection
// ----------------------------
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// ----------------------------
// Handle form submit
// ----------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $_SESSION['message'] = "⚠️ Invalid request (CSRF check failed).";
        header("Location: index.php");
        exit();
    }

    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title !== "" && $content !== "") {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $id);

        if ($stmt->execute()) {
            unset($_SESSION['csrf_token']); // ✅ Refresh token after success
            $_SESSION['message'] = "✅ Post updated successfully.";
            header("Location: index.php");
            exit();
        } else {
            $error = "❌ Failed to update post.";
        }
        $stmt->close();
    } else {
        $error = "⚠️ Title and content cannot be empty.";
    }
}

// ----------------------------
// View
// ----------------------------
include 'header.php';
?>

<h2>Edit Post</h2>

<?php if (!empty($error)): ?>
    <p class="message error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="title" 
           value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
  
    <textarea name="content" required><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
  
    <!-- ✅ CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

    <button type="submit">Update</button>
</form>

<?php include 'footer.php'; ?>
