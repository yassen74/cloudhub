<?php
require_once __DIR__ . '/adminInclude/session.php';

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

  header('Location: adminDashboard.php');
  exit();
}

header('Location: index.php?err=1');
exit();
