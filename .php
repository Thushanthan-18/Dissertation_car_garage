<?php
// connect.php
$host = 'localhost';
$db = 'car_garage';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<?php
// register.php
require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'customer';

    if (strlen($password) < 8) {
        die('Password must be at least 8 characters');
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $email, $hashed_password, $role]);

    echo "Registration successful.";
}
?>

<?php
// login.php
require 'connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
            exit;
        } else {
            header('Location: customer_dashboard.php');
            exit;
        }
    } else {
        echo "Invalid email or password.";
    }
}
?>

<?php
// logout.php
session_start();
session_destroy();
header('Location: login.html');
exit;
?>

<?php
// book_service.php
require 'connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Access denied. Please log in.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $status = 'pending';

    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, booking_date, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $service_id, $booking_date, $status]);

    echo "Service booked successfully.";
}
?>
