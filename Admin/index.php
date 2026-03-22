<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (isset($_SESSION['admin_email'])) {
  header('Location: adminDashboard.php');
  exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fayen</title>
  <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container" style="max-width: 480px; margin-top: 80px;">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="mb-3">Admin Login</h4>

        <?php if (!empty($_GET['err'])): ?>
          <div class="alert alert-danger">Invalid email or password.</div>
        <?php endif; ?>

        <form method="POST" action="admin.php">
          <input type="hidden" name="checkLogemail" value="1">

          <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" name="adminLogEmail" required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="adminLogPass" required>
          </div>

          <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
