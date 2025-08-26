<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Customer Dashboard</title>
</head>
<body>
  <h1>Welcome, Customer!</h1>
  <p>You are logged in as: <?php echo $_SESSION['role']; ?></p>

  <a href="booking.html">Book a Service</a><br><br>

  <form action="php/logout.php" method="POST">
    <button type="submit">Logout</button>
  </form>
</body>
</html>