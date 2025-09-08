<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// ----------------------------
// Search & Pagination Settings
// ----------------------------
$limit = 5; // posts per page
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Build WHERE clause if searching
$where = "";
$params = [];
if (!empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    $where  = "WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

// Total rows (for pagination links)
$countQuery = "SELECT COUNT(*) AS total FROM posts $where";
$countRes   = mysqli_query($conn, $countQuery);
$total      = mysqli_fetch_assoc($countRes)['total'];
$totalPages = ceil($total / $limit);

// Fetch paged posts
$sql = "SELECT * FROM posts $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// ✅ Include header
include 'header.php';
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?> 🎉</h2>
<hr>

<!-- Search Form -->
<form method="get" action="" class="search-form">
    <input type="text" name="q" placeholder="Search by title or content"
           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    <button type="submit">Search</button>
</form>

<h3>All Posts</h3>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="post">
            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            <small>Posted on: <?php echo htmlspecialchars($row['created_at']); ?></small><br>
            <a href="edit.php?id=<?php echo $row['id']; ?>">✏️ Edit</a> | 
            <a href="delete.php?id=<?php echo $row['id']; ?>">🗑️ Delete</a>
        </div>
        <hr>
    <?php endwhile; ?>
<?php else: ?>
    <p>No posts found.</p>
<?php endif; ?>

<!-- Pagination Links -->
<div class="pagination">
    <?php
    if ($totalPages > 1) {
        for ($i = 1; $i <= $totalPages; $i++) {
            $link = "?page=$i";
            if (!empty($_GET['q'])) $link .= "&q=" . urlencode($_GET['q']);
            $active = $i == $page ? "class='active-page'" : "";
            echo "<a href='$link' $active>$i</a> ";
        }
    }
    ?>
</div>

<?php include 'footer.php'; ?>
