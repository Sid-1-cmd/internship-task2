<?php
session_start();
require 'db.php';

// ----------------------------
// Pagination settings
// ----------------------------
$limit = 5; // posts per page
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = $page && $page > 0 ? $page : 1;
$offset = ($page - 1) * $limit;

// ----------------------------
// Search handling
// ----------------------------
$search = "";
$whereClause = "";
$params = [];
$types = "";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = trim($_GET['search']);
    $whereClause = "WHERE title LIKE CONCAT('%', ?, '%') OR content LIKE CONCAT('%', ?, '%')";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

// ----------------------------
// Count total posts (for pagination)
// ----------------------------
$countSql = "SELECT COUNT(*) AS total FROM posts $whereClause";
$countStmt = $conn->prepare($countSql);

if ($whereClause) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result()->fetch_assoc();
$totalPosts = (int)$countResult['total'];
$totalPages = ceil($totalPosts / $limit);
$countStmt->close();

// ----------------------------
// Fetch posts
// ----------------------------
$sql = "SELECT p.id, p.title, p.content, p.created_at, u.username 
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        $whereClause
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($whereClause) {
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$posts = $stmt->get_result();
$stmt->close();

include 'header.php';
?>

<div class="container">
    <h2>Posts</h2>

    <!-- ✅ Flash message -->
    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- ✅ Search form -->
    <form method="get" action="index.php" class="search-form">
        <input type="text" name="search" placeholder="Search posts..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- ✅ Posts listing -->
    <?php if ($posts->num_rows > 0): ?>
        <?php while ($row = $posts->fetch_assoc()): ?>
            <div class="post">
                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                <small>By <?php echo htmlspecialchars($row['username']); ?> 
                       on <?php echo htmlspecialchars($row['created_at']); ?></small>
                <br>
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['user'] === $row['username']): ?>
                        <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                        <a href="delete.php?id=<?php echo $row['id']; ?>&csrf=<?php echo $_SESSION['csrf_token']; ?>"
                           onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>

    <!-- ✅ Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
               class="<?php echo $i == $page ? 'active' : ''; ?>">
               <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
