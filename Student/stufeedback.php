<?php
if(!isset($_SESSION)){
  session_start();
}

define('TITLE', 'Feedback');
define('PAGE', 'feedback');
include('./stuInclude/header.php');
include_once('../dbConnection.php');
$isLoggedIn = isset($_SESSION['is_login']) && !empty($_SESSION['stuLogEmail']);
$stuEmail = $isLoggedIn ? $_SESSION['stuLogEmail'] : '';

$stuId = null;
$msg = '';
$fContent = '';

if($isLoggedIn){
  $stuEmailSafe = $conn->real_escape_string($stuEmail);
  $sql = "SELECT stu_id FROM student WHERE stu_email='$stuEmailSafe' LIMIT 1";
  $result = $conn->query($sql);
  if($result && $result->num_rows == 1){
    $row = $result->fetch_assoc();
    $stuId = (int)$row['stu_id'];
  }
}

if(isset($_POST['submitFeedbackBtn'])){
  $fContent = trim($_POST['f_content'] ?? '');

  if($fContent === ''){
    $msg = '<div class="alert alert-warning mt-3" role="alert">Please write your feedback first.</div>';
  } elseif(!$isLoggedIn) {
    $msg = '<div class="alert alert-warning mt-3" role="alert">Please login first to submit feedback.</div>';
  } elseif($stuId === null) {
    $msg = '<div class="alert alert-danger mt-3" role="alert">Student account not found. Please login again.</div>';
  } else {
    $fContentSafe = $conn->real_escape_string($fContent);
    $insert = "INSERT INTO feedback (f_content, stu_id) VALUES ('$fContentSafe', $stuId)";
    if($conn->query($insert) === TRUE){
      $msg = '<div class="alert alert-success mt-3" role="alert">Submitted Successfully</div>';
      $fContent = '';
    } else {
      $msg = '<div class="alert alert-danger mt-3" role="alert">Unable to Submit</div>';
    }
  }
}
?>

<div class="col-sm-9 col-md-10 mt-4">
  <section class="student-feedback-section">
    <div class="feedback-page-shell">
      <div class="feedback-surface">
        <div class="feedback-page-header">
          <span class="feedback-eyebrow">CloudHub Feedback</span>
          <h1 class="feedback-hero-title">Share feedback about your learning experience.</h1>
          <p class="feedback-hero-copy">Use this form to report friction, suggest improvements, or tell us what is working well. Help us refine content, improve the UI, and keep CloudHub feeling polished across every learning flow.</p>
          <div class="feedback-hero-meta" aria-label="Feedback page highlights">
            <span class="feedback-hero-pill"><i class="fas fa-bolt" aria-hidden="true"></i> Product improvements</span>
            <span class="feedback-hero-pill"><i class="fas fa-layer-group" aria-hidden="true"></i> Course clarity</span>
            <span class="feedback-hero-pill"><i class="fas fa-shield-alt" aria-hidden="true"></i> Better usability</span>
          </div>
        </div>

        <div class="row feedback-layout-row">
          <div class="col-12 col-xl-4 col-lg-5 mb-4 mb-lg-0">
            <div class="feedback-guidance-card" aria-label="Feedback guidance">
              <h2 class="feedback-side-title">What makes feedback useful</h2>
              <div class="feedback-feature-grid">
                <article class="feedback-feature-card">
                  <span class="feedback-feature-icon"><i class="fas fa-crosshairs" aria-hidden="true"></i></span>
                  <h2>Be specific</h2>
                  <p>Mention the course, page, lesson, or action you were taking.</p>
                </article>
                <article class="feedback-feature-card">
                  <span class="feedback-feature-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                  <h2>Describe the impact</h2>
                  <p>Explain whether it affected readability, navigation, learning flow, or trust.</p>
                </article>
                <article class="feedback-feature-card">
                  <span class="feedback-feature-icon"><i class="fas fa-lightbulb" aria-hidden="true"></i></span>
                  <h2>Suggest the better version</h2>
                  <p>If you have an idea for a cleaner UI or clearer content, include it.</p>
                </article>
              </div>
              <div class="feedback-side-note">
                <strong>CloudHub quality loop</strong>
                <span>Your feedback helps shape future content updates and interface improvements.</span>
              </div>
            </div>
          </div>

          <div class="col-12 col-xl-8 col-lg-7">
            <div class="card feedback-card border-0">
              <div class="card-body p-4">
                <div class="feedback-header">
                  <span class="feedback-form-kicker">Feedback Form</span>
                  <h4 class="feedback-title mb-2">Send Feedback</h4>
                  <p class="feedback-subtitle mb-4">Clear feedback helps us improve the platform, content quality, and overall course usability.</p>
                </div>
                <?php if(!$isLoggedIn) { ?>
                  <div class="alert alert-info" role="alert">You can open this page, but you need to login to submit feedback.</div>
                <?php } ?>

                <form method="POST">
                  <div class="form-group">
                    <label for="f_content" class="feedback-label">Your Feedback</label>
                    <p class="feedback-helper-text" id="feedbackHelp">Include course names, lesson details, readability problems, broken UI, or improvement suggestions.</p>
                    <textarea class="form-control feedback-textarea" id="f_content" name="f_content" rows="8" aria-describedby="feedbackHelp" required placeholder="Write your feedback here..."><?php echo htmlspecialchars($fContent, ENT_QUOTES, 'UTF-8'); ?></textarea>
                  </div>
                  <div class="feedback-actions">
                    <button type="submit" class="btn btn-primary feedback-btn feedback-submit-btn mt-2" name="submitFeedbackBtn">Submit Feedback</button>
                    <button type="reset" class="btn btn-outline-secondary feedback-btn feedback-reset-btn mt-2 ml-2">Clear Form</button>
                  </div>
                  <?php if($msg !== '') { echo $msg; } ?>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

</div> <!-- Close Row Div from header file -->
</div> <!-- Close student dashboard shell from header file -->

<?php
include('./stuInclude/footer.php');
?>
