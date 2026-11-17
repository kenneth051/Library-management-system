<html>
  <head>
  </head>
  <body>
    <h1>Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>!</p>
    <a href="../auth/logout.php">Logout</a>
  </body>
</html>
