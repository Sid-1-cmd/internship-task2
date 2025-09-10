<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

// If already logged in, redirect
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "⚠️ Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = "user"; // default role

        // Validate inputs
        if (empty($username) || empty($password)) {
            $message = "⚠️ All fields are required.";
        } elseif (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
            $message = "⚠️ Username must be 3-20 characters (letters, numbers, underscores).";
        } elseif (strlen($password) < 6) {
            $message = "⚠️ Password must be at least 6 characters long.";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $message = "⚠️ Username already taken. Please choose another.";
            } else {
                // Insert new user
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashedPass, $role);

                if ($stmt->execute()) {
                    // Secure session handling
                    session_regenerate_id(true);
                    $_SESSION["user"] = $username;
                    $_SESSION["role"] = $role;
                    $_SESSION["user_id"] = $stmt->insert_id;

                    header("Location: index.php");
                    exit();
                } else {
                    $message = "❌ Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}

include 'header.php';
?>

<div class="container">
    <h2>Register</h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="username" placeholder="Enter username" required>
        <input type="password" name="password" placeholder="Enter password" required>
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<?php include 'footer.php'; ?>
