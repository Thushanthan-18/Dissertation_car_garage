<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method not allowed');
}

$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  exit('Fill all fields.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  exit('Invalid email.');
}

try {
  $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
  $ok = $stmt->execute(array($name, $email, $message));

  header('Content-Type: application/json');
  echo json_encode(array('ok' => $ok, 'msg' => 'Thanks, your message has been sent'));
} catch (Exception $e) {
  http_response_code(500);
  
  echo 'Save failed: ' . $e->getMessage();
}