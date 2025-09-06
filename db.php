<?php
$host = "localhost";
$user = "root"; // default username in XAMPP
$pass = "";     // default password in XAMPP is empty
$dbname = "blog";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
