<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin' || empty($_SESSION['admin_id'])) {
  header('Location: /car_garage/admin/login_a.php');
  exit;
}
$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - AutoCare Pro</title>
  <link rel="stylesheet" href="../css/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
</head>
<body class="admin-dashboard">
  <nav class="admin-nav">
    <div class="nav-header">
      <img src="../images/logo.png" class="admin-logo" alt="AutoCare Pro Logo">
      <h1>Admin Portal</h1>
    </div>
    <div class="nav-links">
      <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="inventory.php"><i class="fas fa-car"></i> Vehicle Inventory</a>
      <a href="bookings.php"><i class="fas fa-calendar-check"></i> Service Bookings</a>
      <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
      <a href="staff.php"><i class="fas fa-user-tie"></i> Staff Management</a>
      <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
    </div>
    <div class="nav-footer">
      <div class="admin-profile">
        <img src="../images/admin-avatar.jpg" alt="Admin Avatar">
        <div class="admin-info">
          <span class="admin-name"><?php echo htmlspecialchars($adminName); ?></span>
          <span class="admin-role">Administrator</span>
        </div>
      </div>
      <a href="/car_garage/admin/login_a.php" class="logout-btn">
  <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </nav>

  <main class="admin-main">
    <!-- your existing widgets/cards here -->
  </main>

  <script src="../js/admin.js"></script>
</body>
</html>