<?php
require_once 'connect.php';

$username = "Test User";
$email = "thushanthan2518@gmail.com";
$phone = "07123456789";
$password = "Password123"; // type exactly like this
$role = "customer";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $email, $phone, $hashedPassword, $role]);

echo "✅ User created successfully with hashed password.";
?>