<?php
session_start();
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
  header('Location: /car_garage/customer/login.php');
  exit;
}
require_once __DIR__ . '/../php_file/connect.php';

// Load fresh name/email (DB is source of truth)
$displayName  = 'Customer';
$displayEmail = '';

$stmt = $pdo->prepare("SELECT username, email FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if ($u) {
  $displayName  = htmlspecialchars($u['username'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
  $displayEmail = htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8');
} else {
  // fallback to session values
  $displayName  = htmlspecialchars($_SESSION['username'] ?? 'Customer', ENT_QUOTES, 'UTF-8');
  $displayEmail = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Dashboard - AutoCare Pro</title>
    <link rel="stylesheet" href="../css/styles.css" />
    <link rel="stylesheet" href="../css/customer.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body class="customer-dashboard">
    <nav class="customer-nav">
      <div class="nav-header">
        <img
          src="../images/logo.png"
          alt="AutoCare Pro Logo"
          class="customer-logo"
        />
        <h1>My Account</h1>
      </div>
      <div class="nav-links">
        <a href="dashboard.html" class="active">
          <i class="fas fa-home"></i>
          Dashboard
        </a>
        <a href="bookings.html">
          <i class="fas fa-calendar-check"></i>
          My Bookings
        </a>
        <a href="vehicles.html">
          <i class="fas fa-car"></i>
          My Vehicles
        </a>
        <a href="service-history.html">
          <i class="fas fa-history"></i>
          Service History
        </a>
        <a href="profile.html">
          <i class="fas fa-user"></i>
          Profile Settings
        </a>
      </div>
      <div class="nav-footer">
        <div class="customer-profile">
          <img src="../images/customer-avatar.jpg" alt="Customer Avatar" />
          <div class="customer-info">
            <span class="customer-name"><?php echo $displayName; ?></span>
            <span class="customer-email"><?php echo $displayEmail; ?></span>
          </div>
        </div>
    <a href="/car_garage/customer/login.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i>
    Logout
    </a>
      </div>
    </nav>

    <main class="customer-main">
      <header class="main-header">
        <h2>Welcome back, <?php echo $displayName; ?>!</h2>
        <div class="header-actions">
          <button class="notification-btn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">2</span>
          </button>
        </div>
      </header>

      <div class="dashboard-content">
        <!-- Quick Actions -->
        <div class="quick-actions">
          <a href="#" class="action-card">
            <i class="fas fa-calendar-plus"></i>
            <span>Book Service</span>
          </a>
          <a href="#" class="action-card">
            <i class="fas fa-car-side"></i>
            <span>Add Vehicle</span>
          </a>
          <a href="#" class="action-card">
            <i class="fas fa-history"></i>
            <span>View History</span>
          </a>
          <a href="#" class="action-card">
            <i class="fas fa-phone"></i>
            <span>Contact Us</span>
          </a>
        </div>

        <!-- Upcoming Bookings -->
        <div class="dashboard-card">
          <div class="card-header">
            <h3>Upcoming Bookings</h3>
            <a href="bookings.html" class="view-all">View All</a>
          </div>
          <div class="booking-list">
            <div class="booking-item">
              <div class="booking-info">
                <h4>Full Service</h4>
                <p>BMW 3 Series (AB12 CDE)</p>
                <span class="booking-time">Tomorrow, 10:00 AM</span>
              </div>
              <div class="booking-actions">
                <button class="reschedule-btn">Reschedule</button>
                <button class="cancel-btn">Cancel</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Vehicle Summary -->
        <div class="dashboard-grid">
          <div class="dashboard-card">
            <div class="card-header">
              <h3>My Vehicles</h3>
              <a href="vehicles.html" class="view-all">View All</a>
            </div>
            <div class="vehicle-list">
              <div class="vehicle-item">
                <img src="../images/cars/bmw-3-series.jpg" alt="BMW 3 Series" />
                <div class="vehicle-info">
                  <h4>BMW 3 Series</h4>
                  <p>AB12 CDE</p>
                  <span class="service-due">Service due in 2 weeks</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Services -->
          <div class="dashboard-card">
            <div class="card-header">
              <h3>Recent Services</h3>
              <a href="service-history.html" class="view-all">View All</a>
            </div>
            <div class="service-list">
              <div class="service-item">
                <div class="service-info">
                  <h4>MOT Test</h4>
                  <p>BMW 3 Series</p>
                  <span class="service-date">Completed on 15 Feb 2025</span>
                </div>
                <span class="service-status passed">Passed</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <script src="../js/customer.js"></script>
  </body>
</html>
