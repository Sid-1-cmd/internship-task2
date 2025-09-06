<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM posts WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index.php");
exit();
?>
