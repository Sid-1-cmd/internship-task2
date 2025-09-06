<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM posts WHERE id=$id");
$post = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
    $stmt->bind_param("ssi", $title, $content, $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}

include 'header.php';
?>

<h2>Edit Post</h2>
<form method="POST">
  <input type="text" name="title" value="<?php echo $post['title']; ?>" required><br>
  <textarea name="content" required><?php echo $post['content']; ?></textarea><br>
  <button type="submit">Update</button>
</form>

<?php include 'footer.php'; ?>
