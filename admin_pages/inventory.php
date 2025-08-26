<?php
if (session_status()===PHP_SESSION_NONE){ session_start(); }
if (!isset($_SESSION['user_id'])){ header("Location: ../admin/login_a.php"); exit; }
require_once __DIR__ . '/../php_file/connect.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* CSRF */
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['csrf'];

$msg = null; $err = null;

/* Handle price update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $err = "Invalid session token.";
  } elseif ($action === 'update_price') {
    $id    = (int)($_POST['service_id'] ?? 0);
    $price = trim($_POST['price'] ?? '');
    if ($id <= 0) {
      $err = "Invalid service id.";
    } elseif ($price === '' || !is_numeric($price) || $price < 0) {
      $err = "Enter a valid non-negative price.";
    } else {
      $upd = $pdo->prepare("UPDATE services SET price = :p WHERE id = :id");
      $upd->execute([':p' => (float)$price, ':id' => $id]);
      if ($upd->rowCount() >= 0) { $msg = "Price updated for service #{$id}."; }
    }
  }
}

/* filters */
$service = $_GET['service'] ?? 'all';
$price   = $_GET['price']   ?? 'all';
$sort    = $_GET['sort']    ?? 'newest';

/* dropdown data */
$names  = $pdo->query("SELECT DISTINCT name FROM services WHERE name<>'' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$prices = $pdo->query("SELECT DISTINCT price FROM services ORDER BY price")->fetchAll(PDO::FETCH_COLUMN);

$orderSql = [
  'newest'     => 'ORDER BY id DESC',
  'price_asc'  => 'ORDER BY price ASC, id DESC',
  'price_desc' => 'ORDER BY price DESC, id DESC',
][$sort] ?? 'ORDER BY id DESC';

/* query */
$sql = "SELECT id, name, description, price FROM services";
$where = []; $params = [];
if ($service !== 'all') { $where[] = "name = :name";   $params[':name']  = $service; }
if ($price   !== 'all') { $where[] = "price = :price"; $params[':price'] = (float)$price; }
if ($where) $sql .= ' WHERE '.implode(' AND ', $where);
$sql .= " $orderSql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$total = count($rows);
$avg   = $total ? array_sum(array_map(fn($r)=>(float)$r['price'], $rows)) / $total : 0.0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin • Vehicle Inventory</title>
<link rel="stylesheet" href="../css/app.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
  .page { max-width:1100px; margin:0 auto; padding:16px; }
  .card { background:#fff; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,.06); padding:16px; }
  .page-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
  .page-head h1 { margin:0; font-size:22px; display:flex; align-items:center; gap:10px; }
  .meta { display:flex; gap:8px; flex-wrap:wrap; }
  .pill { background:#f1f5f9; border:1px solid #e2e8f0; color:#0f172a; padding:6px 10px; border-radius:999px; font-size:13px; }
  .pill i { margin-right:6px; color:#0ea5e9; }

  .toolbar { display:grid; grid-template-columns:repeat(5, minmax(160px,1fr)) auto; gap:10px; align-items:end; margin:10px 0 14px; }
  @media (max-width:900px){ .toolbar { grid-template-columns:1fr 1fr; } }
  .field { display:flex; flex-direction:column; gap:6px; }
  .form-control { border:1px solid #cbd5e1; border-radius:10px; padding:10px 12px; width:100%; }
  .btn { display:inline-flex; align-items:center; gap:8px; background:#111; color:#fff; border:0; border-radius:10px; padding:10px 14px; cursor:pointer; text-decoration:none; }
  .btn.alt { background:#f8fafc; color:#0f172a; border:1px solid #e2e8f0; }
  .btn.small { padding:8px 10px; border-radius:8px; font-size:13px; }

  .alert { padding:10px 12px; border-radius:10px; margin-bottom:12px; }
  .alert.success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
  .alert.error { background:#fef2f2; color:#7f1d1d; border:1px solid #fecaca; }

  .table-wrap { overflow:auto; border-radius:12px; border:1px solid #e5e7eb; }
  table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; }
  thead th { background:#f8fafc; text-align:left; padding:12px; font-weight:600; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
  tbody td { padding:12px; border-top:1px solid #f1f5f9; vertical-align:middle; }
  tbody tr:hover { background:#fcfcfd; }
  .price { font-weight:700; }
  .desc { color:#475569; max-width:520px; }
  .price-form { display:flex; gap:8px; align-items:center; }
  .price-input { width:110px; }
</style>
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="page-head">
        <h1><i class="fas fa-car"></i> Vehicle Inventory</h1>
        <div class="meta">
          <span class="pill"><i class="fas fa-list"></i> <?= (int)$total ?> items</span>
          <span class="pill"><i class="fas fa-pound-sign"></i> Avg price £<?= number_format($avg, 2) ?></span>
          <?php if ($service!=='all'): ?><span class="pill"><i class="fas fa-tag"></i> <?= e($service) ?></span><?php endif; ?>
          <?php if ($price!=='all'):   ?><span class="pill"><i class="fas fa-sterling-sign"></i> £<?= e(number_format((float)$price,2)) ?></span><?php endif; ?>
        </div>
      </div>

      <?php if ($msg): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($err): ?><div class="alert error"><?= e($err) ?></div><?php endif; ?>

      <form class="toolbar" method="get">
        <div class="field">
          <label>Service</label>
          <select class="form-control" name="service">
            <option value="all" <?= $service==='all'?'selected':'' ?>>All services</option>
            <?php foreach ($names as $n): ?>
              <option value="<?= e($n) ?>" <?= $service===$n?'selected':'' ?>><?= e($n) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Price</label>
          <select class="form-control" name="price">
            <option value="all" <?= $price==='all'?'selected':'' ?>>All prices</option>
            <?php foreach ($prices as $p): ?>
              <option value="<?= e($p) ?>" <?= ((string)$price===(string)$p)?'selected':'' ?>>£<?= e(number_format((float)$p,2)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Sort by</label>
          <select class="form-control" name="sort">
            <option value="newest"     <?= $sort==='newest'?'selected':'' ?>>Newest</option>
            <option value="price_asc"  <?= $sort==='price_asc'?'selected':'' ?>>Price (Low → High)</option>
            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price (High → Low)</option>
          </select>
        </div>

        <div class="field">
          <button class="btn" type="submit"><i class="fas fa-filter"></i> Apply</button>
        </div>

        <?php if ($service!=='all' || $price!=='all' || $sort!=='newest'): ?>
          <div class="field">
            <a class="btn alt" href="inventory.php"><i class="fas fa-rotate"></i> Reset</a>
          </div>
        <?php endif; ?>
      </form>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:70px">ID</th>
              <th style="width:220px">Name</th>
              <th>Description</th>
              <th style="width:220px">Price (£) / Update</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="4" class="empty">
                <i class="fas fa-inbox"></i>No services match those filters.
              </td></tr>
            <?php else: foreach($rows as $r): ?>
              <tr>
                <td><?= e($r['id']) ?></td>
                <td><?= e($r['name']) ?></td>
                <td class="desc"><?= e($r['description']) ?></td>
                <td>
                  <form method="post" class="price-form">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="action" value="update_price">
                    <input type="hidden" name="service_id" value="<?= e($r['id']) ?>">
                    <input class="form-control price-input" type="number" name="price" step="0.01" min="0" value="<?= e(number_format((float)$r['price'], 2, '.', '')) ?>">
                    <button class="btn small" type="submit"><i class="fas fa-save"></i> Save</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>