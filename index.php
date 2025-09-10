<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Do NOT override role here – it must be set at login time
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// ----------------------------
// Search & Pagination Settings
// ----------------------------
$limit = 5; // posts per page
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['q']) ? trim($_GET['q']) : "";

// ----------------------------
// Count total posts (prepared)
// ----------------------------
if (!empty($search)) {
    $like = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts");
}
$stmt->execute();
$resultCount = $stmt->get_result();
$total = $resultCount->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);
$stmt->close();

// ----------------------------
// Fetch posts (with user info)
// ----------------------------
if (!empty($search)) {
    $stmt = $conn->prepare("
        SELECT posts.*, users.username, users.id AS author_id 
        FROM posts 
        JOIN users ON posts.user_id = users.id
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT posts.*, users.username, users.id AS author_id 
        FROM posts 
        JOIN users ON posts.user_id = users.id
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$posts = $stmt->get_result();
$stmt->close();

// ✅ Include header
include 'header.php';
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?> 🎉</h2>
<hr>

<!-- Search Form -->
<form method="get" action="" class="search-form">
    <input type="text" name="q" placeholder="Search by title or content"
           value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<h3>All Posts</h3>

<?php if ($posts && $posts->num_rows > 0): ?>
    <?php while ($row = $posts->fetch_assoc()): ?>
        <div class="post">
            <h4><?php echo htmlspecialchars($row['title']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
            <small>
                Posted by: <?php echo htmlspecialchars($row['username']); ?> 
                on <?php echo htmlspecialchars($row['created_at']); ?>
            </small><br>

            <!-- Role & Ownership-based actions -->
            <?php if ($userRole === 'admin' || ($userRole === 'editor' && $row['author_id'] == $userId)): ?>
                <a href="edit.php?id=<?php echo $row['id']; ?>">✏️ Edit</a>
            <?php endif; ?>

            <?php if ($userRole === 'admin'): ?>
                | <a href="delete.php?id=<?php echo $row['id']; ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>">🗑️ Delete</a>
            <?php endif; ?>
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
            if (!empty($search)) $link .= "&q=" . urlencode($search);
            $active = $i == $page ? "class='active-page'" : "";
            echo "<a href='$link' $active>$i</a> ";
        }
    }
    ?>
</div>

<?php include 'footer.php'; ?>
