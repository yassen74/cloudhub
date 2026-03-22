<?php
if (!function_exists('fayen_debug_log')) {
    function fayen_debug_log(string $message): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? 'cli';
        error_log('[FayenDebug] ' . $uri . ' ' . $message);
    }
}

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    $sessionStart = microtime(true);
    $sessionOk = @session_start();
    $sessionMs = (int) round((microtime(true) - $sessionStart) * 1000);

    if (!$sessionOk) {
        fayen_debug_log('session_start failed');
    } elseif ($sessionMs >= 500) {
        fayen_debug_log('slow session_start ' . $sessionMs . 'ms');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="theme-color" content="#08111d" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
     <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Style CSS -->
    <link rel="stylesheet" type="text/css" href="/css/style.css?v=1003" />
    <title>CloudHub</title>
  </head>
  <body>
     <!-- Start Nagigation -->
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top ch-navbar">
      <div class="container ch-navbar-shell">
      <a href="index.php" class="navbar-brand">CloudHub</a>
      <span class="navbar-text">Learn And Achieve</span>
      <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#myMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="myMenu">
        <ul class="navbar-nav ml-auto custom-nav">
          <li class="nav-item custom-nav-item"><a href="index.php" class="nav-link">Home</a></li>
          <li class="nav-item custom-nav-item"><a href="courses.php" class="nav-link">Courses</a></li>
          <li class="nav-item custom-nav-item"><a href="paymentstatus.php" class="nav-link">Payment Status</a></li>
          <li class="nav-item custom-nav-item"><a href="Student/stufeedback.php" class="nav-link">Feedback</a></li>
          <li class="nav-item custom-nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
        </ul>
      </div></div>
    </nav> <!-- End Navigation -->
