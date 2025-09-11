<?php
session_start();

// Unset all session variables
$_SESSION = [];
session_unset();

// Destroy session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// Regenerate session ID for extra safety
session_start();
session_regenerate_id(true);

// Flash message
$_SESSION['message'] = "✅ You have been logged out successfully.";

// Redirect to login
header("Location: login.php");
exit();
