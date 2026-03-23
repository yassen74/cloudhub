<?php
$footerBasePath = isset($footerBasePath) && is_string($footerBasePath) ? $footerBasePath : '';
?>
<footer class="ch-footer" role="contentinfo">
  <div class="container">
    <div class="row ch-footer-row">
      <section class="col-lg-5 col-md-12 ch-footer-col ch-footer-col-brand">
        <p class="ch-footer-kicker">Cloud learning platform</p>
        <div class="ch-footer-brand-lockup">
          <span class="ch-footer-brand-mark" aria-hidden="true">
            <i class="fas fa-cloud"></i>
          </span>
          <div class="ch-footer-brand-copy">
            <h2 class="ch-footer-brand">CloudHub</h2>
            <p class="ch-footer-tag">Learn And Achieve</p>
          </div>
        </div>
        <p class="ch-footer-text">
          A modern LMS for cloud, DevOps, cybersecurity, and practical technology learning.
        </p>
      </section>

      <nav class="col-lg-3 col-md-6 col-sm-6 ch-footer-col" aria-label="Footer quick links">
        <h6 class="ch-footer-title">Quick Links</h6>
        <ul class="ch-footer-links">
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'index.php', ENT_QUOTES, 'UTF-8'); ?>"><span>Home</span></a></li>
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'courses.php', ENT_QUOTES, 'UTF-8'); ?>"><span>Courses</span></a></li>
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'contact.php', ENT_QUOTES, 'UTF-8'); ?>"><span>Contact</span></a></li>
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'loginorsignup.php#login', ENT_QUOTES, 'UTF-8'); ?>"><span>Get Started</span></a></li>
        </ul>
      </nav>

      <nav class="col-lg-2 col-md-6 col-sm-6 ch-footer-col" aria-label="Footer resources">
        <h6 class="ch-footer-title">Resources</h6>
        <ul class="ch-footer-links">
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'paymentstatus.php', ENT_QUOTES, 'UTF-8'); ?>"><span>Payment Status</span></a></li>
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'Student/stufeedback.php', ENT_QUOTES, 'UTF-8'); ?>"><span>Feedback</span></a></li>
          <li><a href="<?php echo htmlspecialchars($footerBasePath . 'loginorsignup.php#signup', ENT_QUOTES, 'UTF-8'); ?>"><span>Register</span></a></li>
        </ul>
      </nav>

      <section class="col-lg-2 col-md-12 col-sm-12 ch-footer-col ch-footer-contact-col">
        <h6 class="ch-footer-title">Connect</h6>
        <ul class="ch-footer-links ch-footer-contact-list">
          <li>
            <a href="mailto:yassenashraf372@gmail.com" aria-label="Email CloudHub">
              <span class="ch-footer-icon"><i class="fas fa-envelope"></i></span>
              <span>Email Us</span>
            </a>
          </li>
          <li>
            <a href="https://www.linkedin.com/in/yassen-mekawy-138b18320" target="_blank" rel="noopener noreferrer" aria-label="CloudHub LinkedIn">
              <span class="ch-footer-icon"><i class="fab fa-linkedin-in"></i></span>
              <span>LinkedIn</span>
            </a>
          </li>
        </ul>
      </section>
    </div>

    <div class="ch-footer-bottom">
      <p class="ch-footer-copy">© <?php echo date('Y'); ?> CloudHub. All rights reserved.</p>
    </div>
  </div>
</footer>
<?php if (!defined('FAYEN_FOOTER_EMBED')): ?>
<script type="text/javascript" src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/popper.min.js"></script>
<script type="text/javascript" src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/custom.js?v=1004"></script>
</body>
</html>
<?php endif; ?>
