<?php
if (!defined('FAYEN_FOOTER_EMBED')) {
  define('FAYEN_FOOTER_EMBED', false);
}
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="footer-brand">Fayen</div>
        <div class="footer-tagline">Learn And Achieve</div>
        <p class="footer-desc">
          A modern e-learning platform for cloud, cybersecurity, and technology learning.
        </p>
      </div>

      <div class="footer-col">
        <div class="footer-head">Quick Links</div>
        <ul class="footer-links">
          <li><a href="/index.php">Home</a></li>
          <li><a href="/courses.php">Courses</a></li>
          <li><a href="/contact.php">Contact</a></li>
          <li><a href="/Student/stufeedback.php">Feedback</a></li>
        </ul>
      </div>

      <div class="footer-col footer-contact-col">
        <div class="footer-head">Contact</div>
        <ul class="footer-contact">
          <li><a href="mailto:yassenashraf372@gmail.com">yassenashraf372@gmail.com</a></li>
          <li><a href="tel:01012175752">01012175752</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-divider"></div>

    <div class="footer-copy">
      &copy; <?php echo date('Y'); ?> Fayen. All rights reserved.
    </div>
  </div>
</footer>

<?php if (!FAYEN_FOOTER_EMBED): ?>
</body>
</html>
<?php endif; ?>
