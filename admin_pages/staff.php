<?php
if (session_status()===PHP_SESSION_NONE){session_start();}
if (!isset($_SESSION['user_id'])){ header("Location: ../admin/login_a.php"); exit; }
require_once __DIR__ . '/../php_file/connect.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* CSRF */
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }
$csrf = $_SESSION['csrf'];

$msg = null; $err = null;

/* Handle POST: add / delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $err = "Invalid session token.";
  } else {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
      $username = trim($_POST['username'] ?? '');
      $email    = trim($_POST['email'] ?? '');
      $role     = trim($_POST['role'] ?? '');
      $phone    = trim($_POST['phone'] ?? '');
      $pass     = $_POST['password'] ?? '';
      $staffIdInput = trim($_POST['staff_id'] ?? '');

      // minimal validation
      if ($username === '' || $email === '' || $role === '' || $pass === '') {
        $err = "All fields marked * are required.";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email address.";
      } elseif (strlen($pass) < 8) {
        $err = "Password must be at least 8 characters.";
      } else {
        // unique email?
        $chk = $pdo->prepare("SELECT 1 FROM admin_users WHERE email = :e LIMIT 1");
        $chk->execute([':e'=>$email]);
        if ($chk->fetch()) {
          $err = "Email already exists.";
        } else {
          // Staff ID: use provided value or auto-generate next number
          if ($staffIdInput !== '') {
            $staffId = $staffIdInput;
          } else {
            // If staff_id is INT
            $staffId = (int)$pdo->query("SELECT COALESCE(MAX(staff_id),0)+1 FROM admin_users")->fetchColumn();
            // If staff_id is VARCHAR and you want STF codes, swap with:
            // $n = (int)$pdo->query("SELECT COALESCE(MAX(CAST(staff_id AS UNSIGNED)),0)+1 FROM admin_users")->fetchColumn();
            // $staffId = 'STF'.str_pad((string)$n, 4, '0', STR_PAD_LEFT);
          }

          $hash = password_hash($pass, PASSWORD_BCRYPT);
          $ins = $pdo->prepare("
            INSERT INTO admin_users (staff_id, username, email, password_hash, role, phone)
            VALUES (:sid, :u, :e, :h, :r, :p)
          ");
          $ins->execute([
            ':sid'=>$staffId, ':u'=>$username, ':e'=>$email, ':h'=>$hash,
            ':r'=>$role, ':p'=>$phone
          ]);
          $msg = "Staff user created.";
        }
      }
    } elseif ($action === 'delete') {
      $id = (int)($_POST['admin_id'] ?? 0);
      if ($id === (int)($_SESSION['admin_id'] ?? 0)) {
        $err = "You cannot remove your own account.";
      } elseif ($id > 0) {
        $del = $pdo->prepare("DELETE FROM admin_users WHERE admin_id = :id");
        $del->execute([':id'=>$id]);
        if ($del->rowCount()) { $msg = "Staff user removed."; }
        else { $err = "No change (user not found)."; }
      } else {
        $err = "Invalid user id.";
      }
    }
  }
}

/* Filters */
$q    = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? 'all';

$where = [];
$params = [];
if ($q !== '') { $where[] = "(username LIKE :q OR email LIKE :q OR phone LIKE :q)"; $params[':q']="%$q%"; }
if ($role !== 'all'){ $where[] = "role = :role"; $params[':role'] = $role; }
$wsql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

$sql = "
  SELECT admin_id AS id, username, email, role, phone, created_at, staff_id
  FROM admin_users
  $wsql
  ORDER BY admin_id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles = $pdo->query("SELECT DISTINCT role FROM admin_users WHERE role IS NOT NULL AND role<>'' ORDER BY role")->fetchAll(PDO::FETCH_COLUMN);

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><title>Admin • Staff Management</title>
<link rel="stylesheet" href="../css/app.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
  /* Layout polish */
  .page-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
  .page-head h1{margin:0;display:flex;align-items:center;gap:10px}
  .actions{display:flex;gap:8px;align-items:end;margin:12px 0;flex-wrap:wrap}
  .grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px}
  @media (max-width:1000px){.grid-2{grid-template-columns:1fr}}

  /* Cards */
  .card, .sidecard{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:16px}
  .sidecard h3{margin:0 0 10px 0}

  /* Table polish */
  table.table{border-collapse:separate;border-spacing:0;width:100%;background:#fff;border-radius:12px;overflow:hidden}
  table.table thead th{background:#f3f4f6;text-align:left;padding:10px;font-weight:600}
  table.table tbody td{padding:10px;border-top:1px solid #eee;vertical-align:middle}
  table.table tbody tr:hover{background:#fafafa}

  /* Badges & buttons */
  .badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:.85em;background:#eef;border:1px solid #dde}
  .btn.icon{display:inline-flex;align-items:center;gap:6px}
  .btn.danger{background:#b91c1c;color:#fff}
  .btn{display:inline-block;padding:10px 12px;background:#111;color:#fff;text-decoration:none;border-radius:8px;border:0;cursor:pointer}
  .muted{color:#666;font-size:.9em}

  /* Form grid */
  .form-grid{display:grid;grid-template-columns:1fr;gap:10px}
  .form-grid label{font-weight:600;display:block;margin:4px 0}
  .form-grid .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:720px){.form-grid .row{grid-template-columns:1fr}}
</style>
</head><body>
<div class="container"><div class="card stack">

  <div class="page-head">
    <h1><i class="fas fa-user-tie"></i> Staff Management</h1>
    <a class="btn icon" href="staff.php"><i class="fas fa-rotate"></i> Refresh</a>
  </div>

  <?php if ($msg): ?><div class="alert success"><?=e($msg)?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert error"><?=e($err)?></div><?php endif; ?>

  <div class="grid-2">
    <!-- Left: list + filters -->
    <div class="card">
      <form class="actions" method="get">
        <div>
          <label>Search</label>
          <input class="form-control" type="text" name="q" value="<?=e($q)?>" placeholder="Username, email, phone">
        </div>
        <div>
          <label>Role</label>
          <select class="form-control" name="role">
            <option value="all" <?= $role==='all'?'selected':'' ?>>All roles</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?=e($r)?>" <?= $role===$r?'selected':'' ?>><?=e(ucfirst($r))?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <button class="btn icon" style="margin-top:22px;"><i class="fas fa-filter"></i> Apply</button>
          <?php if($q!==''||$role!=='all'): ?><a class="btn" style="margin-top:22px;" href="staff.php">Reset</a><?php endif; ?>
        </div>
      </form>

      <table class="table">
        <thead>
          <tr>
            <th>Admin ID</th><th>Staff ID</th><th>Username</th><th>Email</th><th>Role</th><th>Phone</th><th>Created</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!$rows): ?>
            <tr><td colspan="8">No staff users found.</td></tr>
          <?php else: foreach($rows as $r): ?>
            <tr>
              <td><?=e($r['id'])?></td>
              <td><?=e($r['staff_id'] ?? '')?></td>
              <td><?=e($r['username'] ?? '')?></td>
              <td><?=e($r['email'] ?? '')?></td>
              <td><span class="badge"><?=e($r['role'] ?? '')?></span></td>
              <td><?=e($r['phone'] ?? '')?></td>
              <td><?=e($r['created_at'] ?? '')?></td>
              <td>
                <form method="post" onsubmit="return confirm('Remove this staff user?');" style="display:inline">
                  <input type="hidden" name="csrf" value="<?=$csrf?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="admin_id" value="<?=e($r['id'])?>">
                  <button class="btn danger icon" type="submit" title="Remove">
                    <i class="fas fa-trash"></i> Remove
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
      <p class="muted">Tip: you can’t remove your own account.</p>
    </div>

    <!-- Right: add staff -->
    <div class="sidecard">
      <h3><i class="fas fa-user-plus"></i> Add New Staff</h3>
      <form method="post" autocomplete="off" class="form-grid">
        <input type="hidden" name="csrf" value="<?=$csrf?>">
        <input type="hidden" name="action" value="add">

        <div class="row">
          <div>
            <label>Username *</label>
            <input class="form-control" type="text" name="username" required>
          </div>
          <div>
            <label>Role *</label>
            <select class="form-control" name="role" required>
              <?php
                $roleOptions = array_unique(array_merge($roles, ['admin','staff','manager']));
                foreach ($roleOptions as $opt):
              ?>
                <option value="<?=e($opt)?>"><?=e(ucfirst($opt))?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div>
            <label>Email *</label>
            <input class="form-control" type="email" name="email" required>
          </div>
          <div>
            <label>Phone</label>
            <input class="form-control" type="text" name="phone" placeholder="+44...">
          </div>
        </div>

        <div class="row">
          <div>
            <label>Password *</label>
            <input class="form-control" type="password" name="password" minlength="8" required>
          </div>
          <div>
            <label>Staff ID (optional)</label>
            <input class="form-control" type="text" name="staff_id" placeholder="Leave blank to auto-assign">
          </div>
        </div>

        <button class="btn icon" type="submit" style="margin-top:6px;">
          <i class="fas fa-save"></i> Create Staff
        </button>
      </form>
    </div>
  </div>

</div></div>
</body></html>