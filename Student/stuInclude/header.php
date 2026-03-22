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
// Student image fallback
if (!isset($stu_img) || !is_string($stu_img) || trim($stu_img) === '') {
    // try common session keys if available
    $stu_img = $_SESSION['stu_img'] ?? $_SESSION['stu_img_path'] ?? '';
}
if (!is_string($stu_img)) { $stu_img = ''; }
if (trim($stu_img) === '') {
    $stu_img = '../image/stu_default.png';
}

// Safe fallback title (do not rely on undefined constants)
if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = 'CloudHub';
}
if (!defined('CloudHub_PAGE_TITLE')) { define('CloudHub_PAGE_TITLE', $pageTitle); }

$pageClass = 'page-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string)PAGE);

include_once('../dbConnection.php');

 if(isset($_SESSION['is_login'])){
  $stuLogEmail = $_SESSION['stuLogEmail'];
 } 
 // else {
 //  echo "<script> location.href='../index.php'; </script>";
 // }
 if(isset($stuLogEmail)){
  $sql = "SELECT stu_img FROM student WHERE stu_email = '$stuLogEmail'";
  $result = $conn->query($sql);
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (!empty($row['stu_img'])) {
      $stu_img = $row['stu_img'];
    }
  }
 }
?>

<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta http-equiv="X-UA-Compatible" content="ie=edge">
 <title>
  <?php echo CloudHub_PAGE_TITLE ?>
 </title>
 <!-- Bootstrap CSS -->
 <link rel="stylesheet" href="../css/bootstrap.min.css">

 <!-- Font Awesome CSS -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

 <!-- Custom CSS -->
 <link rel="stylesheet" href="../css/stustyle.css?v=1002">

</head>

<body class="student-dashboard <?php echo htmlspecialchars($pageClass, ENT_QUOTES, 'UTF-8'); ?>">
 <!-- Top Navbar -->
 <nav class="navbar navbar-dark fixed-top flex-md-nowrap p-0 shadow student-topbar" style="background-color: #225470;">
  <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="../index.php">CloudHub</a>
 </nav>

 <!-- Side Bar -->
 <div class="container-fluid mb-5 student-dashboard-shell" style="margin-top:40px;">
  <div class="row">
   <nav class="col-sm-2 bg-light sidebar py-5 d-print-none student-sidebar">
    <div class="sidebar-sticky">
     <ul class="nav flex-column">
      <li class="nav-item mb-3">
      <img src="<?php echo $stu_img ?>" alt="studentimage" class="img-thumbnail rounded-circle">
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'profile') {echo 'active';} ?>" href="studentProfile.php">
        <i class="fas fa-user"></i>
        Profile <span class="sr-only">(current)</span>
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'mycourse') {echo 'active';} ?>" href="myCourse.php">
        <i class="fab fa-accessible-icon"></i>
        My Courses
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'feedback') {echo 'active';} ?>" href="stufeedback.php">
        <i class="fab fa-accessible-icon"></i>
        Feedback
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'studentChangePass') {echo 'active';} ?>" href="studentChangePass.php">
        <i class="fas fa-key"></i>
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
