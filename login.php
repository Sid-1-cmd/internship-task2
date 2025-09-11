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

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = "⚠️ Invalid request. Please try again.";
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        if ($username !== "" && $password !== "") {
            // Prepared statement
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();

                // Verify password
                if (password_verify($password, $row["password"])) {
                    // Prevent session fixation
                    session_regenerate_id(true);

                    // Store session securely
                    $_SESSION["user_id"] = (int)$row["id"];
                    $_SESSION["user"] = htmlspecialchars($row["username"], ENT_QUOTES, 'UTF-8');
                    $_SESSION["role"] = $row["role"];

                    // Clear used CSRF token
                    unset($_SESSION['csrf_token']);

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "❌ Invalid username or password.";
                }
            } else {
                $error = "❌ Invalid username or password.";
            }

            $stmt->close();
        } else {
            $error = "⚠️ Please enter both username and password.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Login</h2>
    <?php if (!empty($error)): ?>
        <p class="message error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="username">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<?php include 'footer.php'; ?>
