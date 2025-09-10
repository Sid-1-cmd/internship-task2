<?php
session_start();
include 'db.php';

// Require login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Validate ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    exit("⚠️ Invalid post ID.");
}

// Fetch post
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    exit("⚠️ Post not found.");
}

// Check permissions: Admin OR Owner
if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] != $post['user_id']) {
    exit("⛔ You do not have permission to edit this post.");
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit("⚠️ Invalid request.");
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title !== "" && $content !== "") {
        $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "✅ Post updated successfully.";
        header("Location: index.php");
        exit();
    } else {
        $error = "⚠️ Title and content cannot be empty.";
    }
}

include 'header.php';
?>

<h2>Edit Post</h2>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">
  <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>
  <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>
  
  <!-- CSRF Token -->
  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

  <button type="submit">Update</button>
</form>

<?php include 'footer.php'; ?>
