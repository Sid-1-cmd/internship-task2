<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Registration successful! <a href='login.php'>Login here</a></p>";
    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

include 'header.php';
?>

<h2>Register</h2>
<form method="POST">
  <input type="text" name="username" placeholder="Username" required><br>
  <input type="password" name="password" placeholder="Password" required><br>
  <button type="submit">Register</button>
</form>

<?php include 'footer.php'; ?>
