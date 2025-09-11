<?php
$host   = "localhost";
$user   = "root";    // default username in XAMPP
$pass   = "";        // default password in XAMPP
$dbname = "blog";

// Enable error reporting (development only, disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4"); // always use UTF-8
} catch (Exception $e) {
    // Log error for server admin
    error_log("Database connection failed: " . $e->getMessage());

    // Show generic error to user
    http_response_code(500);
    exit("Internal server error. Please try again later.");
}
?>
