<?php
session_name('FAYENADMINSESSID');
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
  'httponly' => true,
  'samesite' => 'Lax'
]);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/../dbConnection.php';

if (!isset($_POST['checkLogemail'])) {
  header('Location: index.php?err=1');
  exit();
}

$email = trim($_POST['adminLogEmail'] ?? '');
$pass  = (string)($_POST['adminLogPass'] ?? '');

if ($email === '' || $pass === '') {
  header('Location: index.php?err=1');
  exit();
}

$pass_md5 = md5($pass);

$stmt = $conn->prepare("SELECT admin_id, admin_name, admin_email, admin_pass FROM admin WHERE admin_email = ? LIMIT 1");
if (!$stmt) {
  header('Location: index.php?err=1');
  exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

if ($row && (hash_equals($row['admin_pass'], $pass) || hash_equals($row['admin_pass'], $pass_md5))) {
  session_regenerate_id(true);
  $_SESSION['admin_id'] = (int)$row['admin_id'];
  $_SESSION['admin_email'] = $row['admin_email'];
  $_SESSION['admin_name'] = $row['admin_name'] ?: 'Admin';

  @file_put_contents('/tmp/fayen_admin_debug.log',
    "ADMIN.PHP OK\n".
    "time=".date('c')."\n".
    "sid=".session_id()."\n".
    "session=".print_r($_SESSION, true)."\n".
    "cookie=".print_r($_COOKIE, true)."\n\n",
    FILE_APPEND
  );

  header('Location: adminDashboard.php');
  exit();
}

@file_put_contents('/tmp/fayen_admin_debug.log',
  "ADMIN.PHP FAIL\n".
  "time=".date('c')."\n".
  "email=".$email."\n\n",
  FILE_APPEND
);

header('Location: index.php?err=1');
exit();
