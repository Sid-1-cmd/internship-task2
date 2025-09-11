<?php
session_start();
include 'db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$title = "";
$content = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "⚠️ Invalid request. Please try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // Validation
        if ($title === '' || $content === '') {
            $error = "⚠️ Please enter both title and content.";
        } elseif (strlen($title) > 255) {
            $error = "⚠️ Title must be 255 characters or less.";
        } else {
            $user_id = $_SESSION['user_id'];

            try {
                $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("ssi", $title, $content, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "✅ Post created successfully.";
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "❌ Unable to save post. Please try again.";
                }
                $stmt->close();
            } catch (Exception $e) {
                error_log("DB Error: " . $e->getMessage());
                $error = "❌ Internal server error. Please try again later.";
            }
        }
    }
}

include 'header.php';
?>

<div class="container">
  <div class="login-container" style="max-width:700px; margin:20px auto; padding:24px;">
    <h2>Create Post</h2>

    <?php if (!empty($error)): ?>
      <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="title">Title</label>
      <input type="text" id="title" name="title" maxlength="255"
             value="<?php echo htmlspecialchars($title); ?>" required>

      <label for="content">Content</label>
      <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>

      <!-- CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <button type="submit">Save Post</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
