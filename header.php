<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make user info available globally
if (!empty($_SESSION['user'])) {
    $userId   = $_SESSION['user_id'] ?? null;
    $userRole = $_SESSION['role'] ?? 'user';
} else {
    $userId   = null;
    $userRole = 'guest';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Blog App</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <span>My Blog App</span>
    <div>
      <a href="index.php">🏠 Home</a>

      <?php if (!empty($_SESSION['user'])): ?>
        <!-- Show Create option to logged-in users -->
        <a href="create.php">➕ Create</a>

        <!-- Admin-only link -->
        <?php if ($userRole === 'admin'): ?>
          <a href="admin.php">⚙️ Admin Panel</a>
        <?php endif; ?>

        <!-- Logout -->
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">🚪 Logout</a>
      <?php else: ?>
        <!-- If not logged in -->
        <a href="login.php">🔑 Login</a>
        <a href="register.php">📝 Register</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Flash message -->
  <?php if (!empty($_SESSION['message'])): ?>
    <div class="flash-message">
      <?php 
        echo htmlspecialchars($_SESSION['message']); 
        unset($_SESSION['message']); // Clear after showing
      ?>
    </div>
  <?php endif; ?>

  <!-- Main container -->
  <div class="container">
