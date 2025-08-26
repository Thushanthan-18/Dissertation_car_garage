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

try {
  $stmt = $pdo->prepare("SELECT user_id, username, email, password_hash, role
                         FROM users
                         WHERE LOWER(email) = LOWER(?)
                         LIMIT 1");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  http_response_code(500);
  exit('DB error.');
}

if (!$user) {
  exit('No user found with that email.');
}

if (!password_verify($password, $user['password_hash'])) {
  exit('Invalid credentials.');
}

// good login → set session
session_regenerate_id(true);
$_SESSION['user_id']  = (int)$user['user_id'];
$_SESSION['role']     = 'customer';               
$_SESSION['username'] = $user['username'] ?? '';
$_SESSION['email']    = $user['email'] ?? '';

// redirect to customer dashboard
header('Location: /car_garage/customer/dashboard.php');
exit;