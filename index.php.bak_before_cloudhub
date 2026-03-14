<?php
include('./dbConnection.php');
include('./mainInclude/header.php');
?>

<!-- Start Video Background-->
<div class="container-fluid remove-vid-marg home-hero">
  <div class="vid-parent">
    <video id="heroVideo" playsinline webkit-playsinline autoplay muted loop preload="metadata" poster="images/hero-poster.jpg">
      <source src="video/hero-light.mp4" type="video/mp4">
    </video>
    <img id="heroFallback" src="images/hero-poster.jpg" alt="Hero Background" style="display:none;">
    <div class="vid-overlay"></div>
  </div>
  <div class="vid-content">
<div class="hero-content">
<h1>Welcome to <span>Fayen</span></h1>
<p>Learn And Achieve</p>
<a href="courses.php" class="hero-btn">Get Started</a>
</div>
  </div>
</div>

<script>
(function () {
  var video = document.getElementById('heroVideo');
  var fallback = document.getElementById('heroFallback');

  if (!video || !fallback) {
    return;
  }

  function showFallback() {
    fallback.style.display = 'block';
    video.style.visibility = 'hidden';
  }

  function showVideo() {
    fallback.style.display = 'none';
    video.style.visibility = 'visible';
  }

  video.addEventListener('loadeddata', showVideo);
  video.addEventListener('playing', showVideo);
  video.addEventListener('error', showFallback);

  var playAttempt = video.play();
  if (playAttempt && typeof playAttempt.catch === 'function') {
    playAttempt.catch(showFallback);
  }
})();
</script>

<div class="homepage-footer">
  <?php include('./mainInclude/footer.php'); ?>
</div>
