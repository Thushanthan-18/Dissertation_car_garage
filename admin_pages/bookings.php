<?php
if (session_status()===PHP_SESSION_NONE){session_start();}
if (!isset($_SESSION['user_id'])){ header("Location: ../admin/login_a.php"); exit; }
require_once __DIR__ . '/../php_file/connect.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Filters */
$status  = $_GET['status']  ?? 'all';
$service = (int)($_GET['service'] ?? 0);
$from    = trim($_GET['from'] ?? '');
$to      = trim($_GET['to']   ?? '');

$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$where = [];
$params = [];
if ($status !== 'all') { $where[] = "b.status = :status"; $params[':status'] = $status; }
if ($service > 0)      { $where[] = "b.service_id = :sid"; $params[':sid'] = $service; }
if ($from !== '')      { $where[] = "b.date >= :from";     $params[':from'] = $from; }
if ($to   !== '')      { $where[] = "b.date <= :to";       $params[':to']   = $to;  }
$wsql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

$sql = "
  SELECT 
    b.id AS booking_id, b.date, b.time, b.status,
    s.name AS service,
    u.username, u.email
  FROM bookings b
  LEFT JOIN users    u ON u.user_id = b.customer_id
  LEFT JOIN services s ON s.id      = b.service_id
  $wsql
  ORDER BY b.date DESC, b.time DESC, b.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function displayName($r){ 
  $n = trim((string)($r['username'] ?? ''));
  return $n !== '' ? $n : (trim((string)($r['email'] ?? '')) ?: 'Unknown');
}
$total = count($rows);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin • Service Bookings</title>
<link rel="stylesheet" href="../css/app.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
  .page{max-width:1100px;margin:0 auto;padding:16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,.06);padding:16px}
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
  .page-head h1{margin:0;display:flex;align-items:center;gap:10px;font-size:22px}
  .meta{display:flex;gap:8px;flex-wrap:wrap}
  .pill{background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a;padding:6px 10px;border-radius:999px;font-size:13px}
  .pill i{margin-right:6px;color:#0ea5e9}

  .filters{display:grid;grid-template-columns:repeat(5,minmax(160px,1fr));gap:10px;align-items:end;margin:8px 0 14px}
  @media (max-width:900px){ .filters{grid-template-columns:1fr 1fr} }
  .field{display:flex;flex-direction:column;gap:6px}
  .form-control{border:1px solid #cbd5e1;border-radius:10px;padding:10px 12px;width:100%}
  .btn{display:inline-flex;align-items:center;gap:8px;background:#111;color:#fff;border:0;border-radius:10px;padding:10px 14px;cursor:pointer;text-decoration:none}
  .btn.alt{background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0}

  .table-wrap{overflow:auto;border-radius:12px;border:1px solid #e5e7eb}
  table{width:100%;border-collapse:separate;border-spacing:0;background:#fff}
  thead th{position:sticky;top:0;background:#f8fafc;text-align:left;padding:12px;font-weight:600;border-bottom:1px solid #e5e7eb;white-space:nowrap;z-index:1}
  tbody td{padding:12px;border-top:1px solid #f1f5f9;vertical-align:middle}
  tbody tr:hover{background:#fcfcfd}
  .customer{font-weight:600}
  .muted{color:#64748b}
  .status{font-weight:600;font-size:.9em;border-radius:999px;padding:6px 10px;border:1px solid}
  .status.Pending{background:#fff7ed;color:#9a3412;border-color:#fdba74}
  .status.Approved{background:#ecfdf5;color:#065f46;border-color:#6ee7b7}
  .status.Completed{background:#eff6ff;color:#1e40af;border-color:#93c5fd}
  .status.Cancelled{background:#fef2f2;color:#7f1d1d;border-color:#fecaca}
  .empty{color:#64748b;text-align:center;padding:24px}
</style>
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="page-head">
        <h1><i class="fas fa-calendar-check"></i> Service Bookings</h1>
        <div class="meta">
          <span class="pill"><i class="fas fa-list"></i> <?= (int)$total ?> records</span>
          <?php if ($status!=='all'):  ?><span class="pill"><i class="fas fa-tag"></i> <?= e($status) ?></span><?php endif; ?>
          <?php if ($service>0):       ?><span class="pill"><i class="fas fa-wrench"></i> Service #<?= (int)$service ?></span><?php endif; ?>
          <?php if ($from!==''):        ?><span class="pill"><i class="fas fa-calendar"></i> From <?= e($from) ?></span><?php endif; ?>
          <?php if ($to!==''):          ?><span class="pill"><i class="fas fa-calendar-day"></i> To <?= e($to) ?></span><?php endif; ?>
        </div>
      </div>

      <form class="filters" method="get">
        <div class="field">
          <label>Status</label>
          <select class="form-control" name="status">
            <?php foreach (['all','Pending','Approved','Completed','Cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Service</label>
          <select class="form-control" name="service">
            <option value="0">All</option>
            <?php foreach($services as $sv): ?>
              <option value="<?= (int)$sv['id'] ?>" <?= $service===(int)$sv['id']?'selected':'' ?>>
                <?= e($sv['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>From</label>
          <input class="form-control" type="date" name="from" value="<?= e($from) ?>">
        </div>
        <div class="field">
          <label>To</label>
          <input class="form-control" type="date" name="to" value="<?= e($to) ?>">
        </div>

        <div class="field">
          <button class="btn" type="submit"><i class="fas fa-filter"></i> Apply</button>
        </div>

        <?php if($status!=='all'||$service>0||$from!==''||$to!==''): ?>
          <div class="field">
            <a class="btn alt" href="bookings.php"><i class="fas fa-rotate"></i> Reset</a>
          </div>
        <?php endif; ?>
      </form>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:80px">ID</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Service</th>
              <th style="width:110px">Date</th>
              <th style="width:100px">Time</th>
              <th style="width:130px">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php if(!$rows): ?>
            <tr><td colspan="7" class="empty"><i class="fas fa-inbox"></i> No bookings found for these filters.</td></tr>
          <?php else: foreach($rows as $r): ?>
            <tr>
              <td><?= e($r['booking_id']) ?></td>
              <td class="customer"><?= e(displayName($r)) ?></td>
              <td class="muted"><?= e($r['email'] ?? '') ?></td>
              <td><?= e($r['service'] ?? '') ?></td>
              <td><?= e($r['date']) ?></td>
              <td><?= e($r['time']) ?></td>
              <td><span class="status <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>