<?php
$host   = "localhost";
$user   = "root";    // default username in XAMPP
$pass   = "";        // default password in XAMPP
$dbname = "blog";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Always use utf8 for consistent encoding
$conn->set_charset("utf8mb4");

// Optional: show errors during development (comment out for production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>