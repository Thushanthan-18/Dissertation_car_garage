<?php
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirmPassword'] ?? '';
$role     = 'customer';

if ($password !== $confirm) {
    exit(' Passwords do not match.');
}
if (strlen($password) < 8) {
    exit(' Password must be at least 8 characters.');
}

$hash = password_hash($password, PASSWORD_BCRYPT);


$check = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    exit(' Email already registered.');
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password_hash, role, created_at, phone)
         VALUES (?, ?, ?, ?, NOW(), ?)"
    );
    $ok = $stmt->execute([$username, $email, $hash, $role, $phone]);
    if ($ok) {
        // after register, redirect to login tab
        header("Location: /car_garage/customer/login.php?#login");
        exit;
    }
    exit(' Registration failed.');
} catch (PDOException $e) {
    http_response_code(500);
    exit('DB error: ' . $e->getMessage());
}