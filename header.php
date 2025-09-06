<!DOCTYPE html>
<html>
<head>
  <title>My Blog App</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <div class="navbar">
    <span>My Blog App</span>
    <div>
      <a href="index.php">🏠 Home</a>
      <a href="create.php">➕ Create</a>
      <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">🚪 Logout</a>
    </div>
  </div>

  <!-- Main container -->
  <div class="container">
