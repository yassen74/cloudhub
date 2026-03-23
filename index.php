<?php
include('./mainInclude/header.php');
?>

<section class="home-hero">
  <div class="container hero-stage">
    <div class="vid-content">
      <div class="hero-content">
        <h1>Welcome to <span>CloudHub</span></h1>
        <div class="hero-actions">
          <a href="loginorsignup.php#login" class="hero-btn">Get Started</a>
          <a href="courses.php" class="hero-btn hero-btn-secondary">Explore Courses</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="home-overview">
  <div class="container">
    <div class="home-overview-grid">
      <article class="home-feature-card">
        <span class="home-feature-icon"><i class="fas fa-cloud" aria-hidden="true"></i></span>
        <h2>Cloud-first learning paths</h2>
        <p>Browse focused tracks for cloud, networking, operating systems, and modern infrastructure skills.</p>
        <a href="courses.php" class="home-feature-link">View learning tracks</a>
      </article>

      <article class="home-feature-card">
        <span class="home-feature-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
        <h2>Professional course experience</h2>
        <p>Move through polished course cards, clear details pages, and a smoother enrollment flow without friction.</p>
        <a href="courses.php" class="home-feature-link">Explore courses</a>
      </article>

      <article class="home-feature-card">
        <span class="home-feature-icon"><i class="fas fa-user-circle" aria-hidden="true"></i></span>
        <h2>Student workspace</h2>
        <p>Use your profile, enrolled courses, feedback tools, and account controls from one consistent student area.</p>
        <a href="loginorsignup.php#login" class="home-feature-link">Access your account</a>
      </article>
    </div>
  </div>
</section>

<div class="homepage-footer">
  <?php include('./mainInclude/footer.php'); ?>
</div>
