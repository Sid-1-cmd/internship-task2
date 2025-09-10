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
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
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

                    $_SESSION["user"] = $row["username"];
                    $_SESSION["role"] = $row["role"]; // admin, editor, user
                    $_SESSION["user_id"] = $row["id"]; // needed for ownership checks

                    header("Location: index.php");
                    exit();
                } else {
                    $error = "❌ Invalid username or password.";
                }
            } else {
                $error = "❌ User not found.";
            }

            $stmt->close();
        } else {
            $error = "⚠️ Please enter both username and password.";
        }
    }
}
?>

<?php include 'header.php'; ?>

<h2>Login</h2>
<?php if (!empty($error)): ?>
    <div style="color:red; font-weight:bold; margin-bottom:10px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<form method="post" action="">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username" required autocomplete="username"><br><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required autocomplete="current-password"><br><br>

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="register.php">Register here</a></p>

<?php include 'footer.php'; ?>
