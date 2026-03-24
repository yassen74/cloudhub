<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
require_once __DIR__ . '/../dbConnection.php';

function wants_json_response(): bool {
  $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
  if (is_string($requestedWith) && strcasecmp($requestedWith, 'XMLHttpRequest') === 0) {
    return true;
  }

  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  return is_string($accept) && stripos($accept, 'application/json') !== false;
}

function out($v) {
  echo json_encode($v);
  exit;
}

$expectsJson = wants_json_response();
if ($expectsJson) {
  header('Content-Type: application/json; charset=UTF-8');
}

$email = trim((string)($_POST['checkLogemail'] ?? $_POST['stuLogEmail'] ?? ''));
$pass  = (string)($_POST['checkLogpass'] ?? $_POST['stuLogPass'] ?? '');

if ($email === '' || $pass === '') {
  if ($expectsJson) {
    out(0);
  }

  header('Location: ../loginorsignup.php#login');
  exit;
}

$stmt = $conn->prepare("SELECT stu_id, stu_pass FROM student WHERE stu_email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
  if ($expectsJson) {
    out(0);
  }

  header('Location: ../loginorsignup.php#login');
  exit;
}

$stuId = (int)$row['stu_id'];
$dbPass = (string)$row['stu_pass'];

$ok = false;

if (password_verify($pass, $dbPass)) {
  $ok = true;
} else {
  // Legacy plaintext support + upgrade
  if (hash_equals($dbPass, $pass)) {
    $ok = true;
    $newHash = password_hash($pass, PASSWORD_BCRYPT);
    $up = $conn->prepare("UPDATE student SET stu_pass = ? WHERE stu_id = ?");
    $up->bind_param("si", $newHash, $stuId);
    $up->execute();
    $up->close();
  }
}

if (!$ok) {
  if ($expectsJson) {
    out(0);
  }

  header('Location: ../loginorsignup.php#login');
  exit;
}

$_SESSION['is_login'] = true;
$_SESSION['stuLogEmail'] = $email;
$_SESSION['stu_email'] = $email;
$_SESSION['stu_id'] = $stuId;

if ($expectsJson) {
  out(1);
}

header('Location: myprofile.php');
exit;
