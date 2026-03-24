<?php
// CloudHub_STU_HEADER_GUARDS
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_start();
    }
}

// Fallbacks to avoid undefined constants/vars
if (!defined('PAGE')) {
    define('PAGE', '');
}
if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = 'CloudHub';
}
$defaultStuImg = '../image/stu/student1.jpg';
$stu_display_name = isset($stu_display_name) && is_string($stu_display_name) && trim($stu_display_name) !== ''
    ? trim($stu_display_name)
    : 'Student';
$stu_display_email = isset($stu_display_email) && is_string($stu_display_email)
    ? trim($stu_display_email)
    : '';
// Student image fallback
if (!isset($stu_img) || !is_string($stu_img) || trim($stu_img) === '') {
    // try common session keys if available
    $stu_img = $_SESSION['stu_img'] ?? $_SESSION['stu_img_path'] ?? '';
}
if (!is_string($stu_img)) { $stu_img = ''; }
if (trim($stu_img) === '') {
    $stu_img = $defaultStuImg;
}

// Safe fallback title (do not rely on undefined constants)
if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = 'CloudHub';
}
if (!defined('CloudHub_PAGE_TITLE')) { define('CloudHub_PAGE_TITLE', $pageTitle); }

$pageClass = 'page-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string)PAGE);
$studentCompactShell = (PAGE === 'feedback');
$studentShellClosingMarkup = $studentCompactShell ? '</div>' : '</div></div>';

include_once('../dbConnection.php');

$stuLogEmail = '';
if (!empty($_SESSION['stu_email']) && is_string($_SESSION['stu_email'])) {
  $stuLogEmail = trim($_SESSION['stu_email']);
} elseif (!empty($_SESSION['stuLogEmail']) && is_string($_SESSION['stuLogEmail'])) {
  $stuLogEmail = trim($_SESSION['stuLogEmail']);
}

if ($stuLogEmail !== '' && ($stu_img === $defaultStuImg || $stu_display_name === 'Student' || $stu_display_email === '')) {
  $stmt = $conn->prepare("SELECT stu_img, stu_name, stu_email FROM student WHERE stu_email = ? LIMIT 1");
  if ($stmt) {
    $stmt->bind_param("s", $stuLogEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if (!empty($row['stu_img'])) {
        $stu_img = (string) $row['stu_img'];
      }
      if (!empty($row['stu_name'])) {
        $stu_display_name = trim((string) $row['stu_name']);
      }
      if (!empty($row['stu_email'])) {
        $stu_display_email = trim((string) $row['stu_email']);
      }
    }
    $stmt->close();
  }
}

if (trim($stu_img) === '') {
  $stu_img = $defaultStuImg;
}
$_SESSION['stu_img'] = $stu_img;
?>

<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta http-equiv="X-UA-Compatible" content="ie=edge">
 <meta name="theme-color" content="#08111d">
 <title>
  <?php echo CloudHub_PAGE_TITLE ?>
 </title>
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
 <!-- Bootstrap CSS -->
 <link rel="stylesheet" href="../css/bootstrap.min.css">

 <!-- Font Awesome CSS -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

 <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet">

 <!-- Shared Site CSS -->
 <link rel="stylesheet" href="../css/style.css?v=1009">

 <!-- Custom CSS -->
 <link rel="stylesheet" href="../css/stustyle.css?v=1010">

</head>

<body class="student-dashboard <?php echo htmlspecialchars($pageClass, ENT_QUOTES, 'UTF-8'); ?>">
 <!-- Top Navbar -->
 <nav class="navbar navbar-dark fixed-top flex-md-nowrap p-0 shadow student-topbar" style="background-color: #225470;">
  <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="../index.php">CloudHub</a>
 </nav>

<?php if ($studentCompactShell): ?>
 <div class="container-fluid student-dashboard-shell student-dashboard-shell-compact" style="margin-top:40px;">
<?php else: ?>
 <!-- Side Bar -->
 <div class="container-fluid mb-5 student-dashboard-shell" style="margin-top:40px;">
  <div class="row">
   <nav class="col-sm-2 bg-light sidebar py-5 d-print-none student-sidebar">
   <div class="sidebar-sticky">
     <ul class="nav flex-column">
      <li class="nav-item mb-3">
        <div class="student-sidebar-profile">
          <img src="<?php echo htmlspecialchars($stu_img, ENT_QUOTES, 'UTF-8'); ?>" alt="studentimage" class="img-thumbnail rounded-circle" decoding="async" onerror="this.onerror=null;this.src='../image/stu/student1.jpg';">
          <div class="student-sidebar-identity">
            <strong class="student-sidebar-name"><?php echo htmlspecialchars($stu_display_name, ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="student-sidebar-email"><?php echo htmlspecialchars($stu_display_email !== '' ? $stu_display_email : $stuLogEmail, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'profile') {echo 'active';} ?>" href="myprofile.php">
        <i class="fas fa-user"></i>
        Profile <span class="sr-only">(current)</span>
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'mycourse') {echo 'active';} ?>" href="myCourse.php">
        <i class="fas fa-play-circle"></i>
        My Courses
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'feedback') {echo 'active';} ?>" href="stufeedback.php">
        <i class="fas fa-comment-dots"></i>
        Feedback
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'studentChangePass') {echo 'active';} ?>" href="studentChangePass.php">
        <i class="fas fa-shield-alt"></i>
        Change Password
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link" href="../logout.php">
        <i class="fas fa-sign-out-alt"></i>
        Logout
       </a>
      </li>
     </ul>
    </div>
   </nav>
<?php endif; ?>
