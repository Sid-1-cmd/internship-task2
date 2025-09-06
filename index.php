<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

// ✅ Include header
include 'header.php';
?>

<h2>Welcome, <?php echo $_SESSION['user']; ?> 🎉</h2>
<hr>
<h3>All Posts</h3>

<?php while($row = $result->fetch_assoc()): ?>
  <div class="post">
    <h4><?php echo $row['title']; ?></h4>
    <p><?php echo $row['content']; ?></p>
    <small>Posted on: <?php echo $row['created_at']; ?></small><br>
    <a href="edit.php?id=<?php echo $row['id']; ?>">✏️ Edit</a> | 
    <a href="delete.php?id=<?php echo $row['id']; ?>">🗑️ Delete</a>
  </div>
<?php endwhile; ?>

<?php include 'footer.php'; ?>
