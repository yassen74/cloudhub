<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../dbConnection.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['checkLogemail'])) {
  echo json_encode(0);
  exit;
}

$email = trim($_POST['adminLogEmail'] ?? '');
$pass  = (string)($_POST['adminLogPass'] ?? '');

if ($email === '' || $pass === '') {
  echo json_encode(0);
  exit;
}

$pass_md5 = md5($pass);

$stmt = $conn->prepare("SELECT admin_id, admin_name, admin_email, admin_pass FROM admin WHERE admin_email = ? LIMIT 1");
if (!$stmt) {
  echo json_encode(0);
  exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

if ($row && (hash_equals($row['admin_pass'], $pass) || hash_equals($row['admin_pass'], $pass_md5))) {
  $_SESSION['is_admin_login'] = true;
  $_SESSION['admin_id'] = (int)$row['admin_id'];
  $_SESSION['admin_email'] = $row['admin_email'];
  $_SESSION['admin_name'] = $row['admin_name'] ?: 'Admin';
  echo json_encode(1);
  exit;
}

echo json_encode(0);
