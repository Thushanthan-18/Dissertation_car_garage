<?php
session_start();
if (empty($_SESSION['admin_user_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
  http_response_code(403);
  exit('Forbidden');
}
