<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

include_once('../dbConnection.php');
header('Content-type: application/json');

function json_out($v) { echo json_encode($v); exit; }
function is_valid_email($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }

function normalize_track_name(string $trackName): string {
  $normalized = strtolower(trim($trackName));
  $aliases = [
    'ai' => 'AI',
    'artificial intelligence' => 'AI',
    'cyber security' => 'Cyber Security',
    'cybersecurity' => 'Cyber Security',
    'aws cloud' => 'AWS Cloud',
    'data science' => 'Data Science',
    'devops' => 'DevOps',
    'networking' => 'Networking',
    'operating systems' => 'Operating Systems',
    'programming' => 'Programming',
  ];

  return $aliases[$normalized] ?? trim($trackName);
}

function resolve_signup_track(mysqli $conn, string $trackName): string {
  $trackName = normalize_track_name($trackName);
  if ($trackName === '') {
    return '';
  }

  foreach (['track', 'tracks'] as $table) {
    $safeTable = $conn->real_escape_string($table);
    $existsRes = $conn->query("SHOW TABLES LIKE '{$safeTable}'");
    $exists = $existsRes && $existsRes->num_rows > 0;
    if ($existsRes) {
      $existsRes->close();
    }

    if (!$exists) {
      continue;
    }

    $stmt = $conn->prepare("SELECT track_name FROM {$table} WHERE LOWER(track_name) = LOWER(?) LIMIT 1");
    if (!$stmt) {
      continue;
    }

    $stmt->bind_param("s", $trackName);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if ($res) {
      $res->close();
    }
    $stmt->close();

    if ($row && !empty($row['track_name'])) {
      return trim((string) $row['track_name']);
    }
  }

  return '';
}

if (isset($_POST['stuemail']) && isset($_POST['checkemail'])) {
  $stuemail = trim((string)$_POST['stuemail']);
  if ($stuemail === '' || !is_valid_email($stuemail)) {
    json_out(0);
  }

  $stmt = $conn->prepare("SELECT 1 FROM student WHERE stu_email = ? LIMIT 1");
  if (!$stmt) json_out(0);
  $stmt->bind_param("s", $stuemail);
  $stmt->execute();
  $res = $stmt->get_result();
  $exists = ($res && $res->num_rows > 0) ? 1 : 0;
  $stmt->close();
  json_out($exists);
}

if (isset($_POST['stusignup'], $_POST['stuname'], $_POST['stuemail'], $_POST['stupass'])) {
  $stuname  = trim((string)$_POST['stuname']);
  $stuemail = trim((string)$_POST['stuemail']);
  $stupass  = (string)$_POST['stupass'];
  $preferredTrack = isset($_POST['preferred_track']) ? resolve_signup_track($conn, (string) $_POST['preferred_track']) : '';

  if ($stuname === '' || $stuemail === '' || $stupass === '' || !is_valid_email($stuemail)) {
    json_out(["status" => "error", "message" => "Invalid input."]);
  }

  if ($preferredTrack === '') {
    json_out(["status" => "error", "message" => "Please select a valid track."]);
  }

  $stmt = $conn->prepare("SELECT 1 FROM student WHERE stu_email = ? LIMIT 1");
  if (!$stmt) json_out(["status" => "error", "message" => "Database prepare failed."]);
  $stmt->bind_param("s", $stuemail);
  $stmt->execute();
  $res = $stmt->get_result();
  $exists = ($res && $res->num_rows > 0);
  $stmt->close();

  if ($exists) {
    json_out(["status" => "duplicate_email", "message" => "Email already registered."]);
  }

  $default_img = "../image/stu/student1.jpg";
  $hash = password_hash($stupass, PASSWORD_BCRYPT);

  $stmt = $conn->prepare("INSERT INTO student (stu_name, stu_email, stu_pass, stu_occ, stu_img) VALUES (?, ?, ?, ?, ?)");
  if (!$stmt) {
    json_out(["status" => "error", "message" => "Insert prepare failed: " . $conn->error]);
  }

  $stmt->bind_param("sssss", $stuname, $stuemail, $hash, $preferredTrack, $default_img);
  $ok = $stmt->execute();

  if (!$ok) {
    $err = $stmt->error;
    $stmt->close();
    json_out(["status" => "error", "message" => "Insert failed: " . $err]);
  }

  $stmt->close();
  json_out(["status" => "ok", "message" => "Registration successful."]);
}

json_out(["status" => "error", "message" => "Invalid request."]);
