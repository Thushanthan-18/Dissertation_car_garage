<?php
session_start();
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if ($email === '' || $password === '') {
  exit('Missing email or password.');
}

// admin lookup
$stmt = $pdo->prepare("
  SELECT admin_id, username, email, password_hash, role
  FROM admin_users
  WHERE LOWER(email) = LOWER(?) AND role = 'admin'
  LIMIT 1
");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || !password_verify($password, $admin['password_hash'])) {
  exit('Invalid admin credentials.');
}

// clear any customer session keys
unset($_SESSION['user_id'], $_SESSION['username']);

// set ALL keys various guards/templates expect
session_regenerate_id(true);
$_SESSION['admin_id']      = (int)$admin['admin_id'];
$_SESSION['admin_user_id'] = (int)$admin['admin_id']; // for _guard.php
$_SESSION['user_id']       = (int)$admin['admin_id']; // for older checks
$_SESSION['admin_name']    = $admin['username'];
$_SESSION['username']      = $admin['username'];
$_SESSION['role']          = 'admin';

header('Location: /car_garage/admin/dashboard.php');
exit;
