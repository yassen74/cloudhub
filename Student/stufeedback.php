<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

define('PAGE', 'feedback');
$pageTitle = 'Feedback';

include_once('../dbConnection.php');

$stuEmail = '';
if (!empty($_SESSION['stu_email']) && is_string($_SESSION['stu_email'])) {
  $stuEmail = trim((string) $_SESSION['stu_email']);
} elseif (!empty($_SESSION['stuLogEmail']) && is_string($_SESSION['stuLogEmail'])) {
  $stuEmail = trim((string) $_SESSION['stuLogEmail']);
}
$isLoggedIn = !empty($_SESSION['is_login']) && $stuEmail !== '';
$stuId = 0;
$msg = '';
$msgType = '';
$fContent = '';

if ($isLoggedIn) {
  $stuStmt = $conn->prepare("SELECT stu_id FROM student WHERE stu_email = ? LIMIT 1");
  $stuStmt->bind_param("s", $stuEmail);
  $stuStmt->execute();
  $stuResult = $stuStmt->get_result()->fetch_assoc();
  $stuStmt->close();
  $stuId = isset($stuResult['stu_id']) ? (int) $stuResult['stu_id'] : 0;
}

if (isset($_POST['submitFeedbackBtn'])) {
  $fContent = trim((string) ($_POST['f_content'] ?? ''));

  if ($fContent === '') {
    $msgType = 'warning';
    $msg = 'Please write your feedback before submitting.';
  } elseif (!$isLoggedIn) {
    $msgType = 'warning';
    $msg = 'Please login first to submit feedback.';
  } elseif ($stuId <= 0) {
    $msgType = 'danger';
    $msg = 'Student account not found. Please login again.';
  } else {
    $feedbackStmt = $conn->prepare("INSERT INTO feedback (f_content, stu_id) VALUES (?, ?)");
    $feedbackStmt->bind_param("si", $fContent, $stuId);
    if ($feedbackStmt->execute()) {
      $msgType = 'success';
      $msg = 'Thanks for the feedback. Your note has been submitted successfully.';
      $fContent = '';
    } else {
      $msgType = 'danger';
      $msg = 'We were unable to submit your feedback right now.';
    }
    $feedbackStmt->close();
  }
}

$stu_display_email = $stuEmail;
include('./stuInclude/header.php');
?>

<main class="student-dashboard-main student-feedback-main">
  <section class="student-feedback-section">
    <div class="feedback-page-shell">
      <div class="feedback-surface feedback-surface-compact">
        <div class="feedback-page-header">
          <span class="feedback-eyebrow">CloudHub Feedback</span>
          <h1 class="feedback-hero-title">Help us improve your learning experience.</h1>
          <p class="feedback-hero-copy">Share anything that affected your course flow, account experience, or overall usability. Clear notes help us keep CloudHub polished and practical.</p>
          <?php if ($isLoggedIn): ?>
            <div class="feedback-page-links">
              <a href="myprofile.php" class="feedback-page-link">Back to My Profile</a>
              <a href="myCourse.php" class="feedback-page-link feedback-page-link-secondary">My Courses</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="card feedback-card border-0">
          <div class="card-body p-4">
            <div class="feedback-header">
              <span class="feedback-form-kicker">Student Feedback</span>
              <h2 class="feedback-title mb-2">Send Feedback</h2>
              <p class="feedback-subtitle mb-0">Tell us what worked well, what felt confusing, or what needs attention next.</p>
            </div>

            <?php if ($isLoggedIn): ?>
              <div class="feedback-account-strip" role="status">
                <span class="feedback-account-label">Signed in as</span>
                <strong><?php echo htmlspecialchars($stuEmail, ENT_QUOTES, 'UTF-8'); ?></strong>
              </div>
            <?php else: ?>
              <div class="alert alert-info mt-3" role="alert">You can view this page, but you need to login before submitting feedback.</div>
            <?php endif; ?>

            <?php if ($msg !== ''): ?>
              <div class="alert alert-<?php echo htmlspecialchars($msgType, ENT_QUOTES, 'UTF-8'); ?> mt-3" role="alert">
                <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>

            <form method="POST" class="feedback-form mt-4">
              <div class="form-group">
                <label for="f_content" class="feedback-label">Your Feedback</label>
                <p class="feedback-helper-text" id="feedbackHelp">Mention the course, page, or action you were taking and describe what would make the experience clearer.</p>
                <textarea class="form-control feedback-textarea" id="f_content" name="f_content" rows="8" aria-describedby="feedbackHelp" required placeholder="Share your feedback here..."><?php echo htmlspecialchars($fContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
              </div>
              <div class="feedback-actions">
                <button type="submit" class="btn btn-primary feedback-btn feedback-submit-btn" name="submitFeedbackBtn">Submit Feedback</button>
                <button type="reset" class="btn btn-outline-secondary feedback-btn feedback-reset-btn">Clear Form</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<?php echo $studentShellClosingMarkup ?? '</div></div>'; ?>

<?php include('./stuInclude/footer.php'); ?>
