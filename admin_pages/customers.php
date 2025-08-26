<?php
if (session_status()===PHP_SESSION_NONE){session_start();}
if (!isset($_SESSION['user_id'])){ header("Location: ../admin/login_a.php"); exit; }
require_once __DIR__ . '/../php_file/connect.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* Filters */
$q     = trim($_GET['q'] ?? '');
$has   = $_GET['has'] ?? 'all';   // all | with | without
$sort  = $_GET['sort'] ?? 'newest';

$orderSql = $sort==='oldest' ? 'ORDER BY u.user_id ASC' : 'ORDER BY u.user_id DESC';

$where = [];
$params = [];
if ($q !== '') {
  $where[] = "(u.username LIKE :q OR u.email LIKE :q)";
  $params[':q'] = "%$q%";
}
$join = '';
if ($has === 'with') {
  $join = "JOIN bookings b ON b.customer_id = u.user_id";
} elseif ($has === 'without') {
  $join = "LEFT JOIN bookings b ON b.customer_id = u.user_id";
  $where[] = "b.id IS NULL";
}
$wsql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

$sql = "
  SELECT u.user_id, u.username, u.email, u.created_at
  FROM users u
  $join
  $wsql
  $orderSql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($rows);
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin • Customers</title>
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

  .filters{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:10px;align-items:end;margin:8px 0 14px}
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
  .empty{color:#64748b;text-align:center;padding:24px}
</style>
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="page-head">
        <h1><i class="fas fa-users"></i> Customers</h1>
        <div class="meta">
          <span class="pill"><i class="fas fa-list"></i> <?= (int)$total ?> records</span>
          <?php if ($q!==''):   ?><span class="pill"><i class="fas fa-search"></i> “<?= e($q) ?>”</span><?php endif; ?>
          <?php if ($has!=='all'): ?><span class="pill"><i class="fas fa-filter"></i> <?= e(ucfirst($has)) ?> bookings</span><?php endif; ?>
          <?php if ($sort!=='newest'): ?><span class="pill"><i class="fas fa-sort"></i> Oldest first</span><?php endif; ?>
        </div>
      </div>

      <form class="filters" method="get">
        <div class="field">
          <label>Search</label>
          <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="Username or email">
        </div>

        <div class="field">
          <label>Booking status</label>
          <select class="form-control" name="has">
            <option value="all"     <?= $has==='all'?'selected':'' ?>>All customers</option>
            <option value="with"    <?= $has==='with'?'selected':'' ?>>With bookings</option>
            <option value="without" <?= $has==='without'?'selected':'' ?>>Without bookings</option>
          </select>
        </div>

        <div class="field">
          <label>Sort</label>
          <select class="form-control" name="sort">
            <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
            <option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Oldest</option>
          </select>
        </div>

        <div class="field">
          <button class="btn" type="submit"><i class="fas fa-filter"></i> Apply</button>
        </div>

        <?php if($q!==''||$has!=='all'||$sort!=='newest'): ?>
          <div class="field">
            <a class="btn alt" href="customers.php"><i class="fas fa-rotate"></i> Reset</a>
          </div>
        <?php endif; ?>
      </form>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:90px">ID</th>
              <th>Username</th>
              <th>Email</th>
              <th style="width:180px">Joined</th>
            </tr>
          </thead>
          <tbody>
          <?php if(!$rows): ?>
            <tr><td colspan="4" class="empty"><i class="fas fa-inbox"></i> No customers found for these filters.</td></tr>
          <?php else: foreach($rows as $r): ?>
            <tr>
              <td><?= e($r['user_id']) ?></td>
              <td class="customer"><?= e($r['username'] ?? '') ?></td>
              <td class="muted"><?= e($r['email'] ?? '') ?></td>
              <td><?= e($r['created_at'] ?? '') ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>