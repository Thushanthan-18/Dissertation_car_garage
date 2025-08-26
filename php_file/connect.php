<?php
$host = 'localhost';
$db   = 'car_garage';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // no echo here
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}