<?php
session_start();
require 'db.php';

// ✅ Check if logged in and role is admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("❌ Access denied. Admins only.");
}

// ✅ Validate and sanitize ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    die("⚠️ Invalid post ID.");
}

try {
    // ✅ Use prepared statement
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "✅ Post deleted successfully.";
    } else {
        $_SESSION['message'] = "⚠️ Post not found or already deleted.";
    }

    $stmt->close();
} catch (Exception $e) {
    // Log error in real apps
    $_SESSION['message'] = "❌ Error deleting post: " . $e->getMessage();
}

header("Location: index.php");
exit;
?>
