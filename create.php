<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

include 'header.php';
?>

<h2>Create Post</h2>
<form method="POST">
  <input type="text" name="title" placeholder="Title" required><br>
  <textarea name="content" placeholder="Content" required></textarea><br>
  <button type="submit">Save Post</button>
</form>

<?php include 'footer.php'; ?>
