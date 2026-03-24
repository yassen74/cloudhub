<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
include('../dbConnection.php');

function resolve_student_context(mysqli $conn, string $stuEmail, int $sessionStuId): array {
  $profileImg = "../image/stu/student1.jpg";
  $resolvedStuId = $sessionStuId;
  $resolvedEmail = $stuEmail;

  if ($resolvedStuId > 0) {
    $stmt = $conn->prepare("SELECT stu_id, stu_email, stu_img FROM student WHERE stu_id = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("i", $resolvedStuId);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($row) {
        if (!empty($row['stu_email'])) {
          $resolvedEmail = trim((string) $row['stu_email']);
        }
        if (!empty($row['stu_img'])) {
          $profileImg = (string) $row['stu_img'];
        }
        return [$resolvedStuId, $resolvedEmail, $profileImg];
      }
    }
  }

  if ($resolvedEmail !== '') {
    $stmt = $conn->prepare("SELECT stu_id, stu_email, stu_img FROM student WHERE stu_email = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("s", $resolvedEmail);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result ? $result->fetch_assoc() : null;
      $stmt->close();
      if ($row) {
        $resolvedStuId = isset($row['stu_id']) ? (int) $row['stu_id'] : 0;
        if (!empty($row['stu_email'])) {
          $resolvedEmail = trim((string) $row['stu_email']);
        }
        if (!empty($row['stu_img'])) {
          $profileImg = (string) $row['stu_img'];
        }
      }
    }
  }

  return [$resolvedStuId, $resolvedEmail, $profileImg];
}

function detect_courseorder_student_column(mysqli $conn): ?string {
  $cols = [];
  $res = $conn->query("SHOW COLUMNS FROM courseorder");
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $cols[] = $row['Field'];
    }
    $res->close();
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

function resolve_student_track(mysqli $conn, int $stuId, string $stuEmail): array {
  $studentTrackName = '';
  $lookupByStuId = $stuId > 0;
  $sql = "SELECT stu_occ FROM student WHERE " . ($lookupByStuId ? "stu_id = ?" : "stu_email = ?") . " LIMIT 1";
  $stmt = $conn->prepare($sql);

  if (!$stmt) {
    return [0, ''];
  }

  if ($lookupByStuId) {
    $stmt->bind_param('i', $stuId);
  } else {
    $stmt->bind_param('s', $stuEmail);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result ? $result->fetch_assoc() : null;
  if ($result) {
    $result->close();
  }
  $stmt->close();

  if ($row && !empty($row['stu_occ'])) {
    $studentTrackName = trim((string) $row['stu_occ']);
  }

  if ($studentTrackName === '') {
    return [0, ''];
  }

  $trackStmt = $conn->prepare("SELECT track_id, track_name FROM tracks WHERE track_name = ? LIMIT 1");
  if (!$trackStmt) {
    return [0, $studentTrackName];
  }

  $trackStmt->bind_param('s', $studentTrackName);
  $trackStmt->execute();
  $trackResult = $trackStmt->get_result();
  $trackRow = $trackResult ? $trackResult->fetch_assoc() : null;
  if ($trackResult) {
    $trackResult->close();
  }
  $trackStmt->close();

  if (!$trackRow) {
    return [0, $studentTrackName];
  }

  return [
    isset($trackRow['track_id']) ? (int) $trackRow['track_id'] : 0,
    isset($trackRow['track_name']) ? trim((string) $trackRow['track_name']) : $studentTrackName,
  ];
}

function course_image_src(array $row): string {
  $raw = isset($row['course_img']) ? trim((string)$row['course_img']) : '';
  $raw = str_replace('\\', '/', $raw);
  $file = $raw !== '' ? basename($raw) : '';

  $defaultWeb = "../image/courseimg/php.jpg";
  if ($file === '') {
    return $defaultWeb;
  }

  $fsPath = __DIR__ . "/../image/courseimg/" . $file;
  if (!is_file($fsPath)) {
    return $defaultWeb;
  }

  return "../image/courseimg/" . $file;
}

$stuEmail = $_SESSION['stu_email'] ?? $_SESSION['stuLogEmail'] ?? '';
$stuEmail = is_string($stuEmail) ? trim($stuEmail) : '';

if ($stuEmail === '') {
  header("Location: ../loginorsignup.php");
  exit;
}

$stuId = isset($_SESSION['stu_id']) ? (int) $_SESSION['stu_id'] : 0;
$courseRows = [];
$recommendedRows = [];
$pageError = '';
$stmt = null;
$recommendationStmt = null;
$addedMessage = isset($_GET['added']) ? 'Course added to your learning path.' : '';
$recommendationFallback = 'No recommendations available yet.';
$recommendedTrackName = '';

[$stuId, $stuEmail, $profileImg] = resolve_student_context($conn, $stuEmail, $stuId);
if ($stuId > 0) {
  $_SESSION['stu_id'] = $stuId;
  $_SESSION['stu_email'] = $stuEmail;
  $_SESSION['stuLogEmail'] = $stuEmail;
  $_SESSION['is_login'] = true;
} else {
  $pageError = 'We could not match your student account. Please sign in again.';
}

/* Fetch purchased courses */
if ($pageError === '') {
  $courseOrderStudentColumn = detect_courseorder_student_column($conn);

  if ($courseOrderStudentColumn === 'stu_id' && $stuId > 0) {
    $sql = "
    SELECT c.course_id, c.course_name, c.course_desc, c.course_price, c.course_img, MAX(o.order_date) AS latest_order_date
    FROM courseorder o
    JOIN course c ON c.course_id = o.course_id
    WHERE o.stu_id = ?
    GROUP BY c.course_id, c.course_name, c.course_desc, c.course_price, c.course_img
    ORDER BY latest_order_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
      $stmt->bind_param('i', $stuId);
    }
  } elseif ($courseOrderStudentColumn === 'stu_email') {
    $sql = "
    SELECT c.course_id, c.course_name, c.course_desc, c.course_price, c.course_img, MAX(o.order_date) AS latest_order_date
    FROM courseorder o
    JOIN course c ON c.course_id = o.course_id
    WHERE o.stu_email = ?
    GROUP BY c.course_id, c.course_name, c.course_desc, c.course_price, c.course_img
    ORDER BY latest_order_date DESC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
      $stmt->bind_param('s', $stuEmail);
    }
  } else {
    $pageError = 'We could not load your courses right now. Please try again later.';
  }

  if ($pageError === '' && !$stmt) {
    $pageError = 'We could not load your courses right now. Please try again later.';
  }

  if ($pageError === '' && $stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $courseRows[] = $row;
      }
      $result->close();
    }
  }
}

if ($stuId > 0) {
  [$recommendedTrackId, $recommendedTrackName] = resolve_student_track($conn, $stuId, $stuEmail);

  if ($recommendedTrackId > 0) {
    $recommendSql = "SELECT * FROM course WHERE track_id = ? ORDER BY course_id ASC";
    $recommendationStmt = $conn->prepare($recommendSql);

    if ($recommendationStmt) {
      $recommendationStmt->bind_param('i', $recommendedTrackId);
      $recommendationStmt->execute();
      $recommendationResult = $recommendationStmt->get_result();

      if ($recommendationResult) {
        while ($row = $recommendationResult->fetch_assoc()) {
          $recommendedRows[] = $row;
        }
        $recommendationResult->close();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CloudHub</title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="../css/style.css?v=1006">
<style>
body {
  background:
    radial-gradient(circle at top left, rgba(34, 211, 238, 0.12), transparent 24%),
    linear-gradient(180deg, #f5f8fc 0%, #edf4fb 100%);
  color: #0f172a;
  font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
  min-height: 100vh;
}

.page-shell{
  max-width:1180px;
  padding:28px 12px 42px;
}

.top-bar{
  max-width:1180px;
  margin:24px auto 0;
  background:linear-gradient(135deg, #08111d 0%, #123863 100%);
  color:#fff;
  padding:16px 20px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:16px;
  border-radius:26px;
  box-shadow:0 26px 48px rgba(2,8,23,0.22);
  border:1px solid rgba(125,211,252,0.16);
}

.top-bar-brand{
  display:flex;
  flex-direction:column;
  gap:4px;
}

.top-bar-brand a{
  color:#fff;
  text-decoration:none;
  font-weight:800;
  font-size:1.3rem;
  letter-spacing:0.01em;
}

.top-bar-subtitle{
  color:rgba(226,232,240,0.82);
  font-size:0.92rem;
}

.profile-section{
  display:flex;
  align-items:center;
  gap:14px;
}

.profile-section img{
  width:48px;
  height:48px;
  border-radius:50%;
  object-fit:cover;
  border:2px solid rgba(255,255,255,0.72);
  box-shadow:0 10px 24px rgba(2,8,23,0.2);
}

.profile-link-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:46px;
  padding:0.8rem 1.15rem;
  border:none;
  border-radius:999px;
  background:linear-gradient(135deg, #ffffff, #dbeafe);
  color:#0f172a !important;
  font-weight:800;
  box-shadow:0 16px 28px rgba(2,8,23,0.16);
}

.profile-link-btn:hover,
.profile-link-btn:focus{
  transform:translateY(-1px);
  text-decoration:none;
  color:#0f172a !important;
}

.page-heading{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
  margin-bottom:24px;
}

.page-kicker{
  display:inline-block;
  margin-bottom:10px;
  font-size:0.8rem;
  font-weight:800;
  letter-spacing:0.14em;
  text-transform:uppercase;
  color:#2563eb;
}

.page-heading h2{
  font-size:clamp(2rem, 3vw, 2.75rem);
  font-weight:800;
  letter-spacing:-0.03em;
  margin-bottom:0.6rem;
}

.page-subtitle{
  max-width:700px;
  margin:0;
  color:#5b6b82;
  line-height:1.75;
}

.browse-link-btn{
  min-height:48px;
  padding:0.85rem 1.2rem;
  border-radius:999px;
  font-weight:700;
  border:1px solid rgba(37,99,235,0.18);
  color:#1d4ed8;
  background:rgba(255,255,255,0.82);
  box-shadow:0 18px 36px rgba(15,23,42,0.08);
}

.course-card{
  background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border-radius:26px;
  padding:18px;
  margin-bottom:22px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:20px;
  border:1px solid rgba(148,163,184,0.16);
  box-shadow:0 22px 42px rgba(15,23,42,0.08);
}

.course-img{
  width:190px;
  height:126px;
  object-fit:cover;
  border-radius:18px;
  box-shadow:0 14px 26px rgba(15,23,42,0.12);
}

.course-info{
  flex:1;
  margin-left:0;
}

.course-info h5{
  font-weight:800;
  font-size:1.32rem;
  margin-bottom:0.5rem;
}

.course-info p{
  color:#5b6b82 !important;
  line-height:1.7;
}

.course-info strong{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:0.55rem 0.85rem;
  border-radius:999px;
  background:rgba(37,99,235,0.08);
  color:#1d4ed8;
  font-weight:800;
}

.course-card .btn-primary{
  min-width:136px;
  min-height:48px;
  border:none;
  border-radius:999px;
  font-weight:700;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:0.85rem 1.2rem;
  background:linear-gradient(135deg, #2563eb, #22d3ee);
  box-shadow:0 18px 34px rgba(37,99,235,0.22);
}

.empty-state{
  background:linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,251,255,0.96));
  border-radius:28px;
  padding:32px 28px;
  border:1px solid rgba(148,163,184,0.14);
  box-shadow:0 24px 42px rgba(15,23,42,0.08);
  text-align:center;
  max-width:640px;
  margin:0 auto 24px;
}

.empty-state h3{
  font-weight:700;
  margin-bottom:10px;
  color:#0f172a;
}

.empty-state p{
  color:#64748b;
  margin-bottom:18px;
  line-height:1.75;
}

.alert{
  border:none;
  border-radius:18px;
  box-shadow:0 18px 30px rgba(15,23,42,0.08);
}

.recommendations-shell{
  margin-top:44px;
  padding:28px;
  border-radius:32px;
  background:linear-gradient(145deg, #08111d 0%, #10263f 58%, #13365b 100%);
  border:1px solid rgba(125,211,252,0.18);
  box-shadow:0 30px 56px rgba(2,8,23,0.22);
}

.recommendations-shell .recommendations-copy{
  max-width:760px;
}

.recommendations-shell .recommendations-heading{
  align-items:flex-start;
  margin-bottom:20px;
}

.recommendations-shell .page-kicker{
  color:#7dd3fc !important;
}

.recommendations-shell .recommendations-title{
  color:#f8fbff;
  margin-bottom:0.55rem;
}

.recommendations-shell .recommendation-subtitle{
  color:rgba(226,232,240,0.88) !important;
  margin:0;
  line-height:1.75;
}

.recommendations-shell .recommendations-grid{
  display:grid !important;
  grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)) !important;
  gap:22px !important;
  align-items:stretch;
  width:100%;
  max-width:1080px;
}

.recommendations-shell .recommendation-card{
  width:auto !important;
  max-width:none !important;
  margin:0 !important;
  padding:16px !important;
  display:flex !important;
  flex-direction:column !important;
  align-items:stretch !important;
  justify-content:flex-start !important;
  gap:14px !important;
  min-height:100%;
  border-radius:24px;
  background:linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  border:1px solid rgba(191,219,254,0.8);
  box-shadow:0 18px 36px rgba(15,23,42,0.12);
}

.recommendations-shell .recommendation-card .course-img{
  width:100% !important;
  height:180px !important;
  border-radius:18px;
  object-fit:cover;
}

.recommendations-shell .recommendation-card .course-info{
  display:flex;
  flex-direction:column;
  gap:10px;
  flex:1;
}

.recommendations-shell .recommendation-card .course-info h5{
  font-size:1.08rem;
  line-height:1.45;
  margin-bottom:0;
  color:#0f172a;
}

.recommendations-shell .recommendation-card .course-info p{
  margin-bottom:0 !important;
  font-size:0.94rem;
  color:#5b6b82 !important;
}

.recommendations-shell .recommendation-card .course-info strong{
  align-self:flex-start;
}

.recommendations-shell .recommendation-card .btn-primary{
  width:100%;
  min-height:46px;
  margin-top:auto;
}

.recommendations-shell .empty-state{
  max-width:none;
  margin:0;
}

@media (max-width: 767.98px) {
  .top-bar{
    margin:16px 12px 0;
    padding:14px 16px;
    gap:10px;
    flex-wrap:wrap;
  }

  .top-bar-brand a{
    font-size:1.08rem;
  }

  .profile-section{
    width:100%;
    justify-content:space-between;
    gap:10px;
  }

  .page-shell{
    padding:24px 12px 34px;
  }

  .page-heading{
    flex-direction:column;
    align-items:flex-start;
  }

  .course-card{
    flex-direction:column;
    align-items:stretch;
    padding:14px;
    gap:14px;
  }

  .course-img{
    width:100%;
    height:180px;
  }

  .course-info{
    margin-left:0;
  }

  .course-card .btn{
    width:100%;
    min-height:44px;
  }

  .recommendations-shell{
    padding:20px 16px;
  }

  .recommendations-shell .recommendations-grid{
    grid-template-columns:1fr !important;
    gap:16px !important;
  }

  .recommendations-shell .recommendation-card .course-img{
    height:180px;
  }
}

@media (min-width: 768px) and (max-width: 991.98px) {
  .recommendations-shell .recommendations-grid{
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
  }
}

@media (min-width: 992px) and (max-width: 1199.98px) {
  .recommendations-shell .recommendations-grid{
    grid-template-columns:repeat(2, minmax(0, 1fr)) !important;
  }
}
</style>
</head>

<body>

<div class="top-bar">
  <div class="top-bar-brand">
    <a href="../index.php">CloudHub</a>
    <span class="top-bar-subtitle">Student learning space</span>
  </div>
  <div class="profile-section">
    <img src="<?php echo htmlspecialchars($profileImg, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile" decoding="async">
    <a href="myprofile.php" class="btn btn-sm profile-link-btn">My Profile</a>
  </div>
</div>

<div class="container page-shell">
<div class="page-heading">
  <div>
    <span class="page-kicker">Student Courses</span>
    <h2>My Courses</h2>
    <p class="page-subtitle">Review your enrolled courses, reopen lessons quickly, and keep your learning path organized from one place.</p>
  </div>
  <a href="../courses.php" class="btn browse-link-btn">Browse Courses</a>
</div>

<?php if ($addedMessage !== ''): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($addedMessage, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if ($pageError !== ''): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?></div>
<?php elseif (count($courseRows) === 0): ?>
  <div class="empty-state">
    <h3>No courses yet</h3>
    <p>Your enrolled courses will appear here once you complete checkout. Explore the catalog and add your first course when you're ready.</p>
    <a href="../courses.php" class="btn btn-primary">Browse Courses</a>
  </div>
<?php endif; ?>

<?php foreach ($courseRows as $row):
  $imgPath = course_image_src($row);
?>
  <div class="course-card">
    <img src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" class="course-img" alt="Course" loading="lazy" decoding="async">
    <div class="course-info">
      <h5><?php echo htmlspecialchars((string)$row['course_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
      <p class="text-muted mb-1"><?php echo htmlspecialchars((string)$row['course_desc'], ENT_QUOTES, 'UTF-8'); ?></p>
      <strong>₹ <?php echo htmlspecialchars((string)$row['course_price'], ENT_QUOTES, 'UTF-8'); ?></strong>
    </div>
    <a href="watchcourse.php?course_id=<?php echo urlencode((string)$row['course_id']); ?>" class="btn btn-primary btn-sm">Open</a>
  </div>
<?php endforeach; ?>

<section class="recommendations-section recommendations-shell">
  <div class="page-heading recommendations-heading">
    <div class="recommendations-copy">
      <span class="page-kicker">Track Suggestions</span>
      <h2 class="recommendations-title">Recommended Courses</h2>
      <p class="recommendation-subtitle">
        <?php if ($recommendedTrackName !== ''): ?>
          Courses related to your selected track: <?php echo htmlspecialchars($recommendedTrackName, ENT_QUOTES, 'UTF-8'); ?>.
        <?php else: ?>
          Courses related to your selected track will appear here when available.
        <?php endif; ?>
      </p>
    </div>
  </div>

  <?php if (count($recommendedRows) === 0): ?>
    <div class="empty-state">
      <h3><?php echo htmlspecialchars($recommendationFallback, ENT_QUOTES, 'UTF-8'); ?></h3>
      <p>We will show related courses here as soon as your selected track is available and there are matching courses beyond your current enrollments.</p>
    </div>
  <?php else: ?>
    <div class="recommendations-grid">
      <?php foreach ($recommendedRows as $row):
        $imgPath = course_image_src($row);
      ?>
        <article class="course-card recommendation-card">
          <img src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" class="course-img" alt="Recommended course" loading="lazy" decoding="async">
          <div class="course-info">
            <h5><?php echo htmlspecialchars((string)$row['course_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
            <p class="text-muted mb-1"><?php echo htmlspecialchars((string)$row['course_desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            <strong>₹ <?php echo htmlspecialchars((string)$row['course_price'], ENT_QUOTES, 'UTF-8'); ?></strong>
          </div>
          <a href="../coursedetails.php?course_id=<?php echo urlencode((string)$row['course_id']); ?>" class="btn btn-primary btn-sm">View Course</a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

</div>
</body>
</html>
<?php
if ($stmt instanceof mysqli_stmt) {
  $stmt->close();
}
if ($recommendationStmt instanceof mysqli_stmt) {
  $recommendationStmt->close();
}
?>
