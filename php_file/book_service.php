<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(' Access denied. Please log in.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $status = 'pending';

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, booking_date, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $service_id, $booking_date, $status]);

    echo "✅ Service booked successfully.";
}
?>