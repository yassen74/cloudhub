<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/dbConnection.php';

$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['admin_email'] ?? '');
  $pass  = $_POST['admin_pass'] ?? '';

  if ($email === '' || $pass === '') {
    $err = "Email and password are required.";
  } else {
    $emailEsc = $conn->real_escape_string($email);
    $passMd5  = md5($pass);

    $sql = "SELECT admin_id, admin_name, admin_email
            FROM admin
            WHERE admin_email='$emailEsc' AND admin_pass='$passMd5'
            LIMIT 1";
    $res = $conn->query($sql);

    if ($res && $res->num_rows === 1) {
      $row = $res->fetch_assoc();
      $_SESSION['is_admin_login'] = true;
      $_SESSION['admin_id'] = $row['admin_id'];
      $_SESSION['admin_email'] = $row['admin_email'];
      $_SESSION['admin_name'] = $row['admin_name'] ?? 'Admin';

      header("Location: Admin/adminDashboard.php");
      exit;
    } else {
      $err = "Invalid credentials.";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container" style="max-width:420px; margin-top:80px;">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3 text-center">Admin Login</h4>

        <?php if ($err !== ""): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <form method="POST" action="adminLogin.php">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="admin_email" type="email" class="form-control" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="admin_pass" type="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>

        <div class="text-center mt-3">
          <a href="index.php" class="text-muted">Back to Home</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
