<?php
session_start();
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'customer')) {
  header('Location: /car_garage/customer/login.php');
  exit;
}
require_once __DIR__ . '/../php_file/connect.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* CSRF */
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['csrf'];

/* Load user display info */
$displayName = 'Customer'; $displayEmail = '';
$stmt = $pdo->prepare("SELECT username, email, phone, password_hash FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);
if ($me) { $displayName = e($me['username'] ?? 'Customer'); $displayEmail = e($me['email'] ?? ''); }

/* Flash */
$msg = null; $err = null;

/* POST handlers */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $err = "Invalid session token.";
  } else {
    $form = $_POST['form'] ?? '';

    if ($form === 'profile_save') {
      $u = trim($_POST['username'] ?? ''); $p = trim($_POST['phone'] ?? '');
      if ($u === '') { $err = "Username is required."; }
      else {
        $upd = $pdo->prepare("UPDATE users SET username=:u, phone=:p WHERE user_id=:id");
        $upd->execute([':u'=>$u, ':p'=>$p, ':id'=>$_SESSION['user_id']]);
        $msg = "Profile updated."; $displayName = e($u); $me['phone'] = $p;
      }
    }

    elseif ($form === 'password_change') {
      $cur = $_POST['current_password'] ?? '';
      $new = $_POST['new_password'] ?? '';
      $rep = $_POST['confirm_password'] ?? '';

      if ($new === '' || strlen($new) < 8) {
        $err = "New password must be at least 8 characters.";
      } elseif ($new !== $rep) {
        $err = "Passwords do not match.";
      } else {
        try {
          $st = $pdo->prepare("SELECT password_hash FROM users WHERE user_id=:id LIMIT 1");
          $st->execute([':id'=>$_SESSION['user_id']]);
          $row = $st->fetch(PDO::FETCH_ASSOC);

          if (!$row || !password_verify($cur, $row['password_hash'] ?? '')) {
            $err = "Current password is incorrect.";
          } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $up = $pdo->prepare("UPDATE users SET password_hash=:h WHERE user_id=:id");
            $up->execute([':h'=>$hash, ':id'=>$_SESSION['user_id']]);
            $msg = "Password updated.";
          }
        } catch (Throwable $e) {
          $err = "Could not update password.";
        }
      }
    }

    elseif ($form === 'book_submit') {
      $sid = (int)($_POST['service_id'] ?? 0);
      $date = trim($_POST['date'] ?? ''); $time = trim($_POST['time'] ?? '');
      if ($sid<=0 || $date==='' || $time==='') { $err = "Service, date and time required."; }
      else {
        $ins = $pdo->prepare("INSERT INTO bookings(customer_id,service_id,`date`,`time`,`status`)
                              VALUES(:cid,:sid,:d,:t,'Pending')");
        $ins->execute([':cid'=>$_SESSION['user_id'], ':sid'=>$sid, ':d'=>$date, ':t'=>$time]);
        $msg = "Booking submitted.";
      }
    }

    elseif ($form === 'cancel_booking') {
      $bid = (int)($_POST['booking_id'] ?? 0);
      $upd = $pdo->prepare("UPDATE bookings SET status='Cancelled'
                            WHERE id=:id AND customer_id=:cid AND status='Pending'");
      $upd->execute([':id'=>$bid, ':cid'=>$_SESSION['user_id']]);
      $msg = $upd->rowCount() ? "Booking cancelled." : "Cannot cancel booking.";
    }
  }
}

/* Helpers */
function get_services(PDO $pdo){
  return $pdo->query("SELECT id,name,price FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
}
function get_bookings(PDO $pdo,$cid){
  $st=$pdo->prepare("SELECT b.id,s.name AS service,b.date,b.time,b.status
                     FROM bookings b JOIN services s ON s.id=b.service_id
                     WHERE b.customer_id=:cid ORDER BY b.date DESC,b.time DESC,b.id DESC");
  $st->execute([':cid'=>$cid]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}
function get_dashboard_stats(PDO $pdo,int $cid): array {
  $stats=['next'=>null,'lastCompleted'=>null,'activeCount'=>0,'vehicleCount'=>0];
  try{
    $st=$pdo->prepare("SELECT s.name AS service,b.date,b.time,b.status
                       FROM bookings b JOIN services s ON s.id=b.service_id
                       WHERE b.customer_id=:cid AND b.status IN('Pending','Approved')
                         AND b.date>=CURDATE()
                       ORDER BY b.date ASC,b.time ASC LIMIT 1");
    $st->execute([':cid'=>$cid]); $stats['next']=$st->fetch(PDO::FETCH_ASSOC)?:null;
  }catch(Throwable $e){}
  try{
    $st=$pdo->prepare("SELECT s.name AS service,b.date
                       FROM bookings b JOIN services s ON s.id=b.service_id
                       WHERE b.customer_id=:cid AND b.status='Completed'
                       ORDER BY b.date DESC,b.time DESC LIMIT 1");
    $st->execute([':cid'=>$cid]); $stats['lastCompleted']=$st->fetch(PDO::FETCH_ASSOC)?:null;
  }catch(Throwable $e){}
  try{
    $st=$pdo->prepare("SELECT COUNT(*) FROM bookings
                       WHERE customer_id=:cid AND status IN('Pending','Approved')");
    $st->execute([':cid'=>$cid]); $stats['activeCount']=(int)$st->fetchColumn();
  }catch(Throwable $e){}
  foreach(['vehicles','user_vehicles'] as $tbl){
    try{ $st=$pdo->prepare("SELECT COUNT(*) FROM {$tbl} WHERE user_id=:cid");
         $st->execute([':cid'=>$cid]); $stats['vehicleCount']=(int)$st->fetchColumn(); break; }
    catch(Throwable $e){}
  }
  return $stats;
}

/* Router */
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Dashboard - AutoCare Pro</title>
  <link rel="stylesheet" href="../css/styles.css"/>
  <link rel="stylesheet" href="../css/customer.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>
    .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:16px;margin-bottom:16px}
    .table{width:100%;border-collapse:collapse}
    .table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
    .table th{background:#f3f4f6}
    .alert{padding:10px;border-radius:8px;margin:8px 0}
    .alert.success{background:#ecfdf5;color:#065f46}
    .alert.error{background:#fef2f2;color:#991b1b}
    .btn{padding:8px 12px;border-radius:8px;background:#111;color:#fff;border:0;cursor:pointer;text-decoration:none}
    .btn.danger{background:#b91c1c}

    /* Quick actions (2 only) */
    .qa-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:12px 0}
    .qa-card{display:flex;align-items:center;gap:12px;justify-content:center;background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-decoration:none;color:#111}
    .qa-card i{font-size:22px}

    /* Summary cards (4) */
    .sum-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:8px}
    @media (max-width:1100px){.sum-grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:640px){.sum-grid{grid-template-columns:1fr}}
    .sum-card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:16px}
    .sum-title{font-weight:600;color:#374151;display:flex;align-items:center;gap:8px;margin-bottom:6px}
    .sum-main{font-size:18px;font-weight:600;margin:4px 0}
    .sum-sub{color:#6b7280}
    .sum-kpi{font-size:28px;font-weight:700;margin-top:6px}

    /* Profile layout */
    .profile-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:16px}
    @media (max-width:900px){.profile-grid{grid-template-columns:1fr}}
    .card h3{margin-top:0}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width:700px){.form-row{grid-template-columns:1fr}}
    .help{color:#6b7280;font-size:.9em;margin-top:6px}
  </style>
</head>
<body class="customer-dashboard">
  <nav class="customer-nav">
    <div class="nav-header">
      <img src="../images/logo.png" alt="AutoCare Pro Logo" class="customer-logo"/>
      <h1>My Account</h1>
    </div>
    <div class="nav-links">
      <a href="dashboard.php?page=home" class="<?= $page==='home'?'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a>
      <a href="dashboard.php?page=bookings" class="<?= $page==='bookings'?'active':'' ?>"><i class="fas fa-calendar-check"></i> My Bookings</a>
      <a href="dashboard.php?page=book" class="<?= $page==='book'?'active':'' ?>"><i class="fas fa-calendar-plus"></i> Book Service</a>
      <a href="dashboard.php?page=history" class="<?= $page==='history'?'active':'' ?>"><i class="fas fa-history"></i> Service History</a>
      <a href="dashboard.php?page=profile" class="<?= $page==='profile'?'active':'' ?>"><i class="fas fa-user"></i> Profile Settings</a>
    </div>
    <div class="nav-footer">
      <div class="customer-profile">
        <img src="../images/customer-avatar.jpg" alt="Customer Avatar"/>
        <div class="customer-info">
          <span class="customer-name"><?= $displayName ?></span>
          <span class="customer-email"><?= $displayEmail ?></span>
        </div>
      </div>
      <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </nav>

  <main class="customer-main">
    <?php if($msg): ?><div class="alert success"><?=e($msg)?></div><?php endif; ?>
    <?php if($err): ?><div class="alert error"><?=e($err)?></div><?php endif; ?>

    <?php if ($page === 'home'): ?>
      <?php $stats = get_dashboard_stats($pdo, (int)$_SESSION['user_id']); ?>
      <header class="main-header">
        <h2>Welcome back, <?= $displayName ?>!</h2>
        <div class="header-actions">
          <button class="notification-btn"><i class="fas fa-bell"></i><span class="notification-badge">2</span></button>
        </div>
      </header>

      <!-- Two actions -->
      <div class="qa-grid">
        <a href="dashboard.php?page=book" class="qa-card"><i class="fas fa-calendar-plus"></i><div>Book Service</div></a>
        <a href="dashboard.php?page=vehicles" class="qa-card"><i class="fas fa-car-side"></i><div>Add Vehicle</div></a>
      </div>

      <!-- Four summary cards -->
      <div class="sum-grid">
        <div class="sum-card">
          <div class="sum-title"><i class="fas fa-clock"></i> Next Booking</div>
          <?php if ($stats['next']): ?>
            <div class="sum-main"><?= e($stats['next']['service']) ?></div>
            <div class="sum-sub"><?= e($stats['next']['date']) ?> @ <?= e($stats['next']['time']) ?> • <?= e($stats['next']['status']) ?></div>
            <a class="btn" href="dashboard.php?page=bookings" style="margin-top:8px;">Manage</a>
          <?php else: ?>
            <div class="sum-main">No upcoming bookings</div>
            <a class="btn" href="dashboard.php?page=book" style="margin-top:8px;">Book now</a>
          <?php endif; ?>
        </div>

        <div class="sum-card">
          <div class="sum-title"><i class="fas fa-list-check"></i> Active Bookings</div>
          <div class="sum-kpi"><?= (int)$stats['activeCount'] ?></div>
          <a class="btn" href="dashboard.php?page=bookings" style="margin-top:8px;">View all</a>
        </div>

        <div class="sum-card">
          <div class="sum-title"><i class="fas fa-wrench"></i> Last Service</div>
          <?php if ($stats['lastCompleted']): ?>
            <div class="sum-main"><?= e($stats['lastCompleted']['service']) ?></div>
            <div class="sum-sub">Completed on <?= e($stats['lastCompleted']['date']) ?></div>
          <?php else: ?>
            <div class="sum-main">No completed services yet</div>
          <?php endif; ?>
        </div>

        <div class="sum-card">
          <div class="sum-title"><i class="fas fa-car"></i> My Vehicles</div>
          <div class="sum-kpi"><?= (int)$stats['vehicleCount'] ?></div>
          <a class="btn" href="dashboard.php?page=vehicles" style="margin-top:8px;">Manage</a>
        </div>
      </div>

    <?php elseif ($page === 'book'): ?>
      <?php $services = get_services($pdo); ?>
      <div class="card">
        <h2>Book a Service</h2>
        <form method="post">
          <input type="hidden" name="csrf" value="<?=$csrf?>"><input type="hidden" name="form" value="book_submit">
          <label>Service</label>
          <select name="service_id" required>
            <option value="">Select…</option>
            <?php foreach($services as $s): ?>
              <option value="<?=$s['id']?>"><?=e($s['name'])?> (£<?=number_format((float)$s['price'],2)?>)</option>
            <?php endforeach; ?>
          </select>
          <label style="margin-top:8px;">Date</label><input type="date" name="date" required>
          <label style="margin-top:8px;">Time</label><input type="time" name="time" required>
          <button class="btn" style="margin-top:10px;">Submit</button>
        </form>
      </div>

    <?php elseif ($page === 'bookings' || $page === 'history'): ?>
      <?php $rows = get_bookings($pdo, $_SESSION['user_id']); ?>
      <div class="card">
        <h2><?= $page==='history' ? 'Service History' : 'My Bookings' ?></h2>
        <table class="table">
          <thead><tr><th>ID</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><?php if($page!=='history'): ?><th>Action</th><?php endif; ?></tr></thead>
          <tbody>
          <?php if(!$rows): ?>
            <tr><td colspan="<?= $page==='history' ? '5' : '6' ?>">No records.</td></tr>
          <?php else: foreach($rows as $r): ?>
            <tr>
              <td><?=e($r['id'])?></td>
              <td><?=e($r['service'])?></td>
              <td><?=e($r['date'])?></td>
              <td><?=e($r['time'])?></td>
              <td><?=e($r['status'])?></td>
              <?php if ($page!=='history'): ?>
              <td>
                <?php if($r['status']==='Pending'): ?>
                  <form method="post" style="display:inline" onsubmit="return confirm('Cancel this booking?');">
                    <input type="hidden" name="csrf" value="<?=$csrf?>">
                    <input type="hidden" name="form" value="cancel_booking">
                    <input type="hidden" name="booking_id" value="<?=$r['id']?>">
                    <button class="btn danger">Cancel</button>
                  </form>
                <?php else: ?>—<?php endif; ?>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($page === 'vehicles'): ?>
      <div class="card">
        <h2>My Vehicles</h2>
        <p>This section is coming soon (vehicle table not required for your current scope).</p>
      </div>

    <?php elseif ($page === 'profile'): ?>
      <div class="profile-grid">
        <!-- Account details -->
        <div class="card">
          <h3><i class="fas fa-user"></i> Account Details</h3>
          <form method="post">
            <input type="hidden" name="csrf" value="<?=$csrf?>">
            <input type="hidden" name="form" value="profile_save">

            <div class="form-row">
              <div>
                <label>Email</label>
                <input type="email" value="<?= $displayEmail ?>" disabled>
                <div class="help">Email is read-only.</div>
              </div>
              <div>
                <label>Username</label>
                <input name="username" value="<?= $displayName ?>" required>
                <div class="help">Shown on your bookings.</div>
              </div>
            </div>

            <div class="form-row" style="margin-top:10px;">
              <div>
                <label>Phone</label>
                <input name="phone" value="<?= e($me['phone'] ?? '') ?>" placeholder="+44...">
              </div>
              <div>
                <label>Preferred Contact</label>
                <select disabled>
                  <option>Email</option>
                  <option>Phone</option>
                  <option>SMS</option>
                </select>
                <div class="help">Coming soon</div>
              </div>
            </div>

            <button class="btn" style="margin-top:12px;">Save Changes</button>
          </form>
        </div>

<!-- Security -->
<div class="card">
  <h3><i class="fas fa-lock"></i> Security</h3>
  <form method="post" autocomplete="off" class="form-grid">
    <input type="hidden" name="csrf" value="<?=$csrf?>">
    <input type="hidden" name="form" value="password_change">

    <div class="row" style="grid-template-columns: 1fr 1fr 1fr;">
      <div>
        <label>Current Password</label>
        <input class="form-control" type="password" name="current_password" required>
      </div>
      <div>
        <label>New Password</label>
        <input class="form-control" type="password" name="new_password" minlength="8" required>
      </div>
      <div>
        <label>Confirm New Password</label>
        <input class="form-control" type="password" name="confirm_password" minlength="8" required>
      </div>
    </div>

    <button class="btn icon" type="submit" style="margin-top:12px;">
      <i class="fas fa-save"></i> Update Password
    </button>
    <div class="help">Use at least 8 characters. Don’t reuse old passwords.</div>
  </form>
</div>

    <?php else: ?>
      <div class="card"><p>Page not found.</p></div>
    <?php endif; ?>
  </main>

  <script src="../js/customer.js"></script>
</body>
</html>