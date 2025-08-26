<?php
session_start();
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
header('Content-Type: application/json');
echo json_encode(['csrf' => $_SESSION['csrf']]);