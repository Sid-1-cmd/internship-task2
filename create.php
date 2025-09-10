<?php
session_start();
include 'db.php';

// Require login
if (!isset($_SESSION['user'])) {
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

        // Basic server-side validation
        if ($title === '' || $content === '') {
            $error = "⚠️ Please enter both title and content.";
        } elseif (strlen($title) > 255) {
            $error = "⚠️ Title must be 255 characters or less.";
        } else {
            // Determine user_id (from session; fallback: lookup by username)
            $user_id = $_SESSION['user_id'] ?? null;
            if ($user_id === null && !empty($_SESSION['user'])) {
                $lookup = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $lookup->bind_param("s", $_SESSION['user']);
                $lookup->execute();
                $r = $lookup->get_result();
                if ($r && $r->num_rows === 1) {
                    $userRow = $r->fetch_assoc();
                    $user_id = (int)$userRow['id'];
                }
                $lookup->close();
            }

            if ($user_id === null) {
                $error = "❌ Could not determine your user ID. Please log out and log in again.";
            } else {
                // Insert post (including user_id)
                $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, NOW())");
                if (!$stmt) {
                    $error = "Database error (prepare): " . htmlspecialchars($conn->error);
                } else {
                    $stmt->bind_param("ssi", $title, $content, $user_id);
                    if ($stmt->execute()) {
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Database error (execute): " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// show form
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

      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <button type="submit">Save Post</button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
