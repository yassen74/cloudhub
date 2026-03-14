<?php
include('./dbConnection.php');
include('./mainInclude/header.php');
?>

<!-- Start Video Background-->
<div class="container-fluid remove-vid-marg home-hero">
  <div class="vid-parent">
    <video autoplay muted loop playsinline preload="auto">
      <source src="video/hero-web.mp4" type="video/mp4">
    </video>
    <img id="heroFallback" src="images/hero-poster.jpg" alt="Hero Background" style="display:none;">
    <div class="vid-overlay"></div>
  </div>
  <div class="vid-content">
<div class="hero-content">
<h1>Welcome to <span>CloudHub</span></h1>
<p>Learn And Achieve</p>
<a href="loginorsignup.php" class="hero-btn">Get Started</a>
</div>
  </div>
</div>

<div class="homepage-footer">
  <?php include('./mainInclude/footer.php'); ?>
</div>
