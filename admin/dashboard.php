<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin' || empty($_SESSION['admin_id'])) {
  header('Location: /car_garage/admin/login_a.php');
  exit;
}
$adminName = $_SESSION['admin_name'] ?? 'Admin';

/* In-dashboard router */
$page = $_GET['page'] ?? 'home';
$routes = [
  'inventory' => '../admin_pages/inventory.php',
  'bookings'  => '../admin_pages/bookings.php',
  'customers' => '../admin_pages/customers.php',
  'staff'     => '../admin_pages/staff.php',
  'reports'   => '../admin_pages/reports.php',
];
$frameSrc = $routes[$page] ?? '';
function active($p, $cur){ return $p===$cur ? 'active' : ''; }

/* --- data for KPIs / chart / recent --- */
require_once __DIR__ . '/../php_file/connect.php';
try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $totalCustomers  = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
  $totalBookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
  $totalServices   = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
  $pendingBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn();
  $todayBookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE `date`=CURDATE()")->fetchColumn();

  $revenue = (float)$pdo->query("
    SELECT COALESCE(SUM(s.price),0)
    FROM bookings b
    JOIN services s ON s.id=b.service_id
    WHERE b.status IN ('Approved','Completed')
  ")->fetchColumn();

  $recent = $pdo->query("
    SELECT b.id, b.date, b.time, b.status,
           s.name AS service,
           COALESCE(NULLIF(u.username,''), u.email, CONCAT('User #', u.user_id)) AS customer,
           u.email
    FROM bookings b
    LEFT JOIN users u ON u.user_id=b.customer_id
    LEFT JOIN services s ON s.id=b.service_id
    ORDER BY b.date DESC, b.time DESC, b.id DESC
    LIMIT 5
  ")->fetchAll(PDO::FETCH_ASSOC);

  $rows7 = $pdo->query("
    SELECT DATE(`date`) d, COUNT(*) c
    FROM bookings
    WHERE `date` >= (CURDATE() - INTERVAL 6 DAY)
    GROUP BY DATE(`date`)
    ORDER BY d
  ")->fetchAll(PDO::FETCH_ASSOC);

  $labels = []; $counts = []; $map = [];
  foreach ($rows7 as $r) { $map[$r['d']] = (int)$r['c']; }
  for ($i=6; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels[] = $d;
    $counts[] = $map[$d] ?? 0;
  }
} catch (Throwable $e) {
  
  $totalCustomers=$totalBookings=$totalServices=$pendingBookings=$todayBookings=0;
  $revenue=0.0; $recent=[]; $labels=[]; $counts=[];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - AutoCare Pro</title>
  <link rel="stylesheet" href="../css/admin.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    
    .admin-main { position: relative; }
    .tab-frame {
      width: 100%; height: calc(100vh - 120px);
      border: 0; background: #fff; border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,.06);
    }
    .welcome-card { background:#fff; padding:24px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.06); }
    .kpi-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:16px; }
    .two-col  { display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px; }
    .btn { display:inline-block; padding:10px 12px; background:#111; color:#fff; text-decoration:none; border-radius:8px; }
    @media (max-width: 1000px){
      .kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
      .two-col{grid-template-columns:1fr;}
    }
  </style>
</head>
<body class="admin-dashboard">
  <nav class="admin-nav">
    <div class="nav-header">
      <img src="../images/logo.png" class="admin-logo" alt="AutoCare Pro Logo">
      <h1>Admin Portal</h1>
    </div>
    <div class="nav-links">
      <a href="dashboard.php?page=home" class="<?php echo active('home',$page); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="dashboard.php?page=inventory" class="<?php echo active('inventory',$page); ?>"><i class="fas fa-car"></i> Vehicle Inventory</a>
      <a href="dashboard.php?page=bookings" class="<?php echo active('bookings',$page); ?>"><i class="fas fa-calendar-check"></i> Service Bookings</a>
      <a href="dashboard.php?page=customers" class="<?php echo active('customers',$page); ?>"><i class="fas fa-users"></i> Customers</a>
      <a href="dashboard.php?page=staff" class="<?php echo active('staff',$page); ?>"><i class="fas fa-user-tie"></i> Staff Management</a>
      <a href="dashboard.php?page=reports" class="<?php echo active('reports',$page); ?>"><i class="fas fa-chart-bar"></i> Reports</a>
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
    <?php if ($page === 'home'): ?>
      <div class="welcome-card" style="margin-bottom:16px;">
        <h2>Welcome, <?php echo htmlspecialchars($adminName); ?></h2>
        <p>Quick overview of recent activity.</p>
      </div>

      <!-- KPIs -->
      <div class="kpi-grid">
        <div class="welcome-card"><h3>Total Customers</h3><p style="font-size:28px;margin:8px 0;"><?php echo number_format($totalCustomers); ?></p></div>
        <div class="welcome-card"><h3>Total Bookings</h3><p style="font-size:28px;margin:8px 0;"><?php echo number_format($totalBookings); ?></p></div>
        <div class="welcome-card"><h3>Total Services</h3><p style="font-size:28px;margin:8px 0;"><?php echo number_format($totalServices); ?></p></div>
        <div class="welcome-card"><h3>Pending</h3><p style="font-size:28px;margin:8px 0;"><?php echo number_format($pendingBookings); ?></p></div>
      </div>

      <!-- Chart + Today/Revenue/Actions -->
      <div class="two-col">
        <div class="welcome-card">
          <h3>Bookings (last 7 days)</h3>
          <canvas id="bookingsChart" height="120"></canvas>
        </div>
        <div class="welcome-card">
          <h3>Today</h3>
          <p>Bookings today: <strong><?php echo number_format($todayBookings); ?></strong></p>
          <h3 style="margin-top:16px;">Revenue (est.)</h3>
          <p>£ <?php echo number_format($revenue, 2); ?></p>
          <h3 style="margin-top:16px;">Quick Actions</h3>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <a class="btn" href="dashboard.php?page=bookings"><i class="fas fa-calendar-check"></i> View Bookings</a>
            <a class="btn" href="dashboard.php?page=inventory"><i class="fas fa-plus"></i> Manage Services</a>
            <a class="btn" href="dashboard.php?page=customers"><i class="fas fa-user"></i> View Customers</a>
            <a class="btn" href="dashboard.php?page=reports"><i class="fas fa-chart-bar"></i> Open Reports</a>
          </div>
        </div>
      </div>

      <!-- Recent bookings -->
      <div class="welcome-card">
        <h3>Recent Bookings</h3>
        <table class="table">
          <thead><tr><th>ID</th><th>Customer</th><th>Email</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (!$recent): ?>
              <tr><td colspan="7">No recent bookings.</td></tr>
            <?php else: foreach ($recent as $r): ?>
              <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['customer'] ?? 'Unknown'); ?></td>
                <td><?php echo htmlspecialchars($r['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($r['service'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($r['date']); ?></td>
                <td><?php echo htmlspecialchars($r['time']); ?></td>
                <td><?php echo htmlspecialchars($r['status']); ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Chart.js -->
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script>
        const labels = <?php echo json_encode($labels); ?>;
        const data   = <?php echo json_encode($counts); ?>;
        const ctx = document.getElementById('bookingsChart').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{ label: 'Bookings', data, tension: 0.3, fill: false }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
          }
        });
      </script>

    <?php elseif ($frameSrc): ?>
    
      <iframe class="tab-frame" src="<?php echo htmlspecialchars($frameSrc); ?>" title="Admin Section"></iframe>
    <?php else: ?>
      <div class="welcome-card"><p>Page not found.</p></div>
    <?php endif; ?>
  </main>

  <script src="../js/admin.js"></script>
</body>
</html>