<?php
if (session_status()===PHP_SESSION_NONE){session_start();}
if (!isset($_SESSION['user_id'])){ header("Location: ../admin/login_a.php"); exit; }
require_once __DIR__ . '/../php_file/connect.php';

$days = (int)($_GET['days'] ?? 7);
if (!in_array($days,[7,30,90],true)) $days = 7;

$params = [':start' => date('Y-m-d', strtotime("-$days days"))];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE `date` >= :start");
$stmt->execute($params);
$totalBookings = (int)$stmt->fetchColumn();

$totalServices  = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$stmt = $pdo->prepare("
  SELECT s.name, COUNT(*) AS cnt
  FROM bookings b
  JOIN services s ON s.id=b.service_id
  WHERE b.`date` >= :start
  GROUP BY s.id
  ORDER BY cnt DESC
  LIMIT 1
");
$stmt->execute($params);
$top = $stmt->fetch(PDO::FETCH_ASSOC);

$series = $pdo->prepare("
  SELECT DATE(`date`) d, COUNT(*) c
  FROM bookings
  WHERE `date` >= :start
  GROUP BY DATE(`date`)
  ORDER BY d
");
$series->execute($params);
$rows = $series->fetchAll(PDO::FETCH_ASSOC);

$labels=[]; $counts=[]; $map=[];
for ($i=$days-1; $i>=0; $i--) { $d=date('Y-m-d', strtotime("-$i day")); $labels[]=$d; }
foreach ($rows as $r){ $map[$r['d']] = (int)$r['c']; }
foreach ($labels as $d){ $counts[] = $map[$d] ?? 0; }

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><title>Admin • Reports</title>
<link rel="stylesheet" href="../css/app.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>.actions{display:flex;gap:8px;align-items:center;margin:12px 0;flex-wrap:wrap}</style>
</head><body><div class="container"><div class="card stack">
  <h1><i class="fas fa-chart-bar"></i> Reports</h1>

  <form class="actions" method="get">
    <label>Period
      <select class="form-control" name="days">
        <option value="7"  <?=$days===7?'selected':''?>>Last 7 days</option>
        <option value="30" <?=$days===30?'selected':''?>>Last 30 days</option>
        <option value="90" <?=$days===90?'selected':''?>>Last 90 days</option>
      </select>
    </label>
    <button class="btn"><i class="fas fa-filter"></i> Apply</button>
    <?php if($days!==7): ?><a class="btn" href="reports.php">Reset</a><?php endif; ?>
  </form>

  <div class="grid cols-2" style="gap:16px;">
    <div class="card">
      <h3>KPIs</h3>
      <ul>
        <li>Total customers: <?=e($totalCustomers)?></li>
        <li>Total services: <?=e($totalServices)?></li>
        <li>Bookings (period): <?=e($totalBookings)?></li>
      </ul>
      <h3>Top service (period)</h3>
      <p><?= $top ? e($top['name'])." (".e($top['cnt']).")" : "No data" ?></p>
    </div>
    <div class="card">
      <h3>Bookings per day</h3>
      <canvas id="repChart" height="120"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = <?=json_encode($labels)?>, data = <?=json_encode($counts)?>;
    const ctx = document.getElementById('repChart').getContext('2d');
    new Chart(ctx, { type:'bar', data:{ labels, datasets:[{ label:'Bookings', data }] },
      options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true, ticks:{precision:0}} } }
    });
  </script>
</div></div></body></html>