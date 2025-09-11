<?php
session_start();
require 'db.php';

// ✅ Require login & admin role
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "❌ Access denied. Admins only.";
    header("Location: index.php");
    exit();
}

// ✅ Validate post ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    $_SESSION['message'] = "⚠️ Invalid post ID.";
    header("Location: index.php");
    exit();
}

// ✅ Validate CSRF token
if (
    !isset($_GET['csrf']) ||
    !hash_equals($_SESSION['csrf_token'], $_GET['csrf'])
) {
    $_SESSION['message'] = "⚠️ Invalid request (CSRF check failed).";
    header("Location: index.php");
    exit();
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

    // ✅ Refresh CSRF token after successful action
    unset($_SESSION['csrf_token']);
} catch (Exception $e) {
    $_SESSION['message'] = "❌ Error deleting post.";
    error_log("Delete error: " . $e->getMessage());
}

header("Location: index.php");
exit();
