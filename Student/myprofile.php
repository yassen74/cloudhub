<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('PAGE', 'profile');
$pageTitle = 'My Profile';

$stuEmail = $_SESSION['stu_email'] ?? $_SESSION['stuLogEmail'] ?? '';
$stuEmail = is_string($stuEmail) ? trim($stuEmail) : '';

if ($stuEmail === '') {
    header('Location: ../loginorsignup.php');
    exit;
}

require_once __DIR__ . '/../dbConnection.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function itv_find_student_email_column(mysqli $conn): string
{
    $candidates = ['stu_email', 'email', 'stuEmail', 'stuLogEmail'];
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM student");
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }

    foreach ($candidates as $candidate) {
        if (in_array($candidate, $cols, true)) {
            return $candidate;
        }
    }

    foreach ($cols as $col) {
        if (stripos($col, 'email') !== false) {
            return $col;
        }
    }

    return 'stu_email';
}

function itv_find_courseorder_student_column(mysqli $conn): ?string
{
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM courseorder");
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }

    if (in_array('stu_id', $cols, true)) {
        return 'stu_id';
    }

    if (in_array('stu_email', $cols, true)) {
        return 'stu_email';
    }

    foreach ($cols as $col) {
        if (stripos($col, 'stu') !== false && stripos($col, 'email') !== false) {
            return $col;
        }
    }

    return null;
}

$student = null;
$courseCount = 0;
$feedbackCount = 0;
$flashType = '';
$flashMessage = '';

if (isset($_GET['ok'])) {
    $flashType = 'success';
    $flashMessage = 'Your profile was updated successfully.';
} elseif (isset($_GET['err']) && is_string($_GET['err']) && trim($_GET['err']) !== '') {
    $flashType = 'danger';
    $flashMessage = trim((string) $_GET['err']);
}

try {
    $emailCol = itv_find_student_email_column($conn);
    $stmt = $conn->prepare("SELECT * FROM student WHERE {$emailCol} = ? LIMIT 1");
    $stmt->bind_param("s", $stuEmail);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($student) {
        $stuId = isset($student['stu_id']) ? (int) $student['stu_id'] : 0;
        if ($stuId > 0) {
            $_SESSION['stu_id'] = $stuId;
        }
        $_SESSION['is_login'] = true;

        $normalizedEmail = isset($student['stu_email']) ? trim((string) $student['stu_email']) : $stuEmail;
        if ($normalizedEmail !== '') {
            $_SESSION['stuLogEmail'] = $normalizedEmail;
            $_SESSION['stu_email'] = $normalizedEmail;
            $stuEmail = $normalizedEmail;
        }

        $courseOrderStudentColumn = itv_find_courseorder_student_column($conn);
        if ($courseOrderStudentColumn === 'stu_id' && $stuId > 0) {
            $courseStmt = $conn->prepare("SELECT COUNT(DISTINCT course_id) AS total_courses FROM courseorder WHERE stu_id = ?");
            $courseStmt->bind_param("i", $stuId);
            $courseStmt->execute();
            $courseResult = $courseStmt->get_result()->fetch_assoc();
            $courseCount = isset($courseResult['total_courses']) ? (int) $courseResult['total_courses'] : 0;
            $courseStmt->close();
        } elseif ($courseOrderStudentColumn === 'stu_email' && $stuEmail !== '') {
            $courseStmt = $conn->prepare("SELECT COUNT(DISTINCT course_id) AS total_courses FROM courseorder WHERE stu_email = ?");
            $courseStmt->bind_param("s", $stuEmail);
            $courseStmt->execute();
            $courseResult = $courseStmt->get_result()->fetch_assoc();
            $courseCount = isset($courseResult['total_courses']) ? (int) $courseResult['total_courses'] : 0;
            $courseStmt->close();
        }

        if ($stuId > 0) {
            $feedbackStmt = $conn->prepare("SELECT COUNT(*) AS total_feedback FROM feedback WHERE stu_id = ?");
            $feedbackStmt->bind_param("i", $stuId);
            $feedbackStmt->execute();
            $feedbackResult = $feedbackStmt->get_result()->fetch_assoc();
            $feedbackCount = isset($feedbackResult['total_feedback']) ? (int) $feedbackResult['total_feedback'] : 0;
            $feedbackStmt->close();
        }
    }
} catch (mysqli_sql_exception $e) {
    $flashType = 'danger';
    $flashMessage = 'We could not load your profile right now. Please try again.';
}

$defaultImage = '../image/stu/student1.jpg';
$stuId = isset($student['stu_id']) ? (int) $student['stu_id'] : 0;
$stuName = isset($student['stu_name']) && trim((string) $student['stu_name']) !== '' ? trim((string) $student['stu_name']) : 'Student';
$stuOcc = isset($student['stu_occ']) ? trim((string) $student['stu_occ']) : '';
$stuImg = isset($student['stu_img']) ? trim((string) $student['stu_img']) : '';
$createdAt = isset($student['created_at']) ? trim((string) $student['created_at']) : '';

$stu_display_name = $stuName;
$stu_display_email = $stuEmail;
$stu_img = $stuImg !== '' ? $stuImg : $defaultImage;
$_SESSION['stu_img'] = $stu_img;

$joinedText = 'Active learner';
if ($createdAt !== '') {
    $timestamp = strtotime($createdAt);
    if ($timestamp !== false) {
        $joinedText = 'Joined ' . date('M Y', $timestamp);
    }
}

include __DIR__ . '/stuInclude/header.php';
?>

<main class="col-sm-10 col-md-10 student-dashboard-main">
  <section class="student-dashboard-section student-profile-section">
    <div class="student-profile-shell">
      <div class="student-profile-hero">
        <div class="student-profile-identity">
          <div class="student-profile-avatar-wrap">
            <img src="<?php echo htmlspecialchars($stu_img, ENT_QUOTES, 'UTF-8'); ?>" alt="Student profile" class="student-profile-avatar" decoding="async" onerror="this.onerror=null;this.src='../image/stu/student1.jpg';">
          </div>
          <div class="student-profile-copy">
            <span class="student-profile-kicker">Student Workspace</span>
            <h1 class="student-profile-title">My Profile</h1>
            <p class="student-profile-subtitle">Manage your CloudHub account, keep your details current, and jump back into your enrolled courses from one clean student hub.</p>
            <div class="student-profile-pills">
              <span class="student-profile-pill"><i class="fas fa-user-circle" aria-hidden="true"></i><?php echo htmlspecialchars($stuName, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="student-profile-pill"><i class="fas fa-envelope" aria-hidden="true"></i><?php echo htmlspecialchars($stuEmail, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="student-profile-pill"><i class="fas fa-star" aria-hidden="true"></i><?php echo htmlspecialchars($joinedText, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
          </div>
        </div>

        <div class="student-profile-actions">
          <a href="myCourse.php" class="btn student-profile-action-btn student-profile-action-primary"><i class="fas fa-play-circle" aria-hidden="true"></i><span>My Courses</span></a>
          <a href="studentChangePass.php" class="btn student-profile-action-btn student-profile-action-secondary"><i class="fas fa-shield-alt" aria-hidden="true"></i><span>Change Password</span></a>
        </div>
      </div>

      <?php if ($flashMessage !== ''): ?>
        <div class="alert alert-<?php echo $flashType === 'success' ? 'success' : 'danger'; ?> student-profile-alert" role="alert">
          <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <?php if (!$student): ?>
        <div class="alert alert-warning student-profile-alert mb-0" role="alert">
          We could not find a student account for this session. Please log in again.
        </div>
      <?php else: ?>
        <div class="row profile-dashboard-row">
          <div class="col-xl-4 col-lg-5 mb-4 mb-lg-0">
            <article class="student-profile-card student-profile-summary-card">
              <div class="student-profile-card-head">
                <span class="student-profile-card-kicker">Account Summary</span>
                <h2>Overview</h2>
              </div>

              <div class="student-profile-stat-grid">
                <div class="student-profile-stat">
                  <strong><?php echo $courseCount; ?></strong>
                  <span>Enrolled courses</span>
                </div>
                <div class="student-profile-stat">
                  <strong><?php echo $feedbackCount; ?></strong>
                  <span>Feedback notes</span>
                </div>
                <div class="student-profile-stat student-profile-stat-focus">
                  <strong><?php echo htmlspecialchars($stuOcc !== '' ? $stuOcc : 'General', ENT_QUOTES, 'UTF-8'); ?></strong>
                  <span>Learning focus</span>
                </div>
              </div>

              <dl class="student-profile-details">
                <div>
                  <dt>Student ID</dt>
                  <dd><?php echo $stuId > 0 ? $stuId : 'Not available'; ?></dd>
                </div>
                <div>
                  <dt>Email</dt>
                  <dd><?php echo htmlspecialchars($stuEmail, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div>
                  <dt>Occupation</dt>
                  <dd><?php echo htmlspecialchars($stuOcc !== '' ? $stuOcc : 'Add your role or focus area', ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
              </dl>

              <div class="student-profile-quick-links">
                <a href="myCourse.php" class="student-profile-link-chip"><i class="fas fa-play-circle" aria-hidden="true"></i><span>Continue Learning</span></a>
                <a href="stufeedback.php" class="student-profile-link-chip"><i class="fas fa-comment-dots" aria-hidden="true"></i><span>Send Feedback</span></a>
                <a href="../logout.php" class="student-profile-link-chip"><i class="fas fa-sign-out-alt" aria-hidden="true"></i><span>Logout</span></a>
              </div>
            </article>
          </div>

          <div class="col-xl-8 col-lg-7">
            <article class="student-profile-card student-profile-form-card" id="profile-editor">
              <div class="student-profile-card-head">
                <span class="student-profile-card-kicker">Update Profile</span>
                <h2>Account details</h2>
                <p>Keep your name, email, occupation, and profile image up to date. Leave the password field blank if you do not want to change it.</p>
              </div>

              <form action="updateprofile.php" method="post" enctype="multipart/form-data" class="student-profile-form">
                <div class="student-form-grid">
                  <div class="form-group">
                    <label for="stu_name">Full Name</label>
                    <input type="text" class="form-control" id="stu_name" name="stu_name" value="<?php echo htmlspecialchars($stuName, ENT_QUOTES, 'UTF-8'); ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="stu_email">Email Address</label>
                    <input type="email" class="form-control" id="stu_email" name="stu_email" value="<?php echo htmlspecialchars($stuEmail, ENT_QUOTES, 'UTF-8'); ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="stu_occ">Occupation</label>
                    <input type="text" class="form-control" id="stu_occ" name="stu_occ" value="<?php echo htmlspecialchars($stuOcc, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Student, Developer, Cloud Engineer">
                  </div>

                  <div class="form-group">
                    <label for="stu_pass">New Password</label>
                    <input type="password" class="form-control" id="stu_pass" name="stu_pass" minlength="6" placeholder="Leave blank to keep your current password">
                  </div>
                </div>

                <div class="form-group mb-0">
                  <label for="stu_img">Profile Image</label>
                  <input type="file" class="form-control-file student-profile-file" id="stu_img" name="stu_img" accept="image/jpeg,image/png,image/webp">
                  <small class="student-form-note">Accepted formats: JPG, PNG, or WebP up to 2MB.</small>
                </div>

                <div class="student-form-actions">
                  <button type="submit" name="update_profile" class="btn student-profile-action-btn student-profile-action-primary">Save Changes</button>
                  <a href="studentChangePass.php" class="btn student-profile-action-btn student-profile-action-secondary">Security Settings</a>
                </div>
              </form>
            </article>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php echo $studentShellClosingMarkup ?? '</div></div>'; ?>

<?php include __DIR__ . '/stuInclude/footer.php'; ?>
