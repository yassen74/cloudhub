<?php
include('./dbConnection.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

function home_course_image_src(array $row): string
{
  $raw = isset($row['course_img']) ? trim((string) $row['course_img']) : '';
  $raw = str_replace('\\', '/', $raw);
  $file = $raw !== '' ? basename($raw) : '';

  if ($file === '') {
    return 'image/courseimg/php.jpg';
  }

  $fsPath = __DIR__ . '/image/courseimg/' . $file;
  if (!is_file($fsPath)) {
    return 'image/courseimg/php.jpg';
  }

  return 'image/courseimg/' . rawurlencode($file);
}

function home_resolve_student_track(mysqli $conn, string $stuEmail, int $stuId): array
{
  if ($stuId <= 0 && $stuEmail === '') {
    return [0, ''];
  }

  $sql = 'SELECT stu_occ FROM student WHERE ' . ($stuId > 0 ? 'stu_id = ?' : 'stu_email = ?') . ' LIMIT 1';
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    return [0, ''];
  }

  if ($stuId > 0) {
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

  $trackName = $row && !empty($row['stu_occ']) ? trim((string) $row['stu_occ']) : '';
  if ($trackName === '') {
    return [0, ''];
  }

  $trackStmt = $conn->prepare('SELECT track_id, track_name FROM tracks WHERE track_name = ? LIMIT 1');
  if (!$trackStmt) {
    return [0, $trackName];
  }

  $trackStmt->bind_param('s', $trackName);
  $trackStmt->execute();
  $trackResult = $trackStmt->get_result();
  $trackRow = $trackResult ? $trackResult->fetch_assoc() : null;
  if ($trackResult) {
    $trackResult->close();
  }
  $trackStmt->close();

  if (!$trackRow) {
    return [0, $trackName];
  }

  return [
    isset($trackRow['track_id']) ? (int) $trackRow['track_id'] : 0,
    isset($trackRow['track_name']) ? trim((string) $trackRow['track_name']) : $trackName,
  ];
}

function home_fetch_track_courses(mysqli $conn, int $trackId): array
{
  if ($trackId <= 0) {
    return [];
  }

  $rows = [];
  $stmt = $conn->prepare("SELECT course_id, course_name, course_desc, course_img FROM course WHERE track_id = ? ORDER BY course_id ASC");
  if (!$stmt) {
    return [];
  }

  $stmt->bind_param('i', $trackId);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    $result->close();
  }
  $stmt->close();

  return $rows;
}

$isStudentLoggedIn = !empty($_SESSION['stu_email']) || !empty($_SESSION['stuLogEmail']);
$stuEmail = isset($_SESSION['stu_email']) ? trim((string) $_SESSION['stu_email']) : trim((string) ($_SESSION['stuLogEmail'] ?? ''));
$stuId = isset($_SESSION['stu_id']) ? (int) $_SESSION['stu_id'] : 0;
$homeRecommendedRows = [];
$homeRecommendationKicker = 'Student Picks';
$homeRecommendationSubtitle = 'Courses aligned with your selected track.';
$studentTrackName = '';

if ($isStudentLoggedIn) {
  [$studentTrackId, $studentTrackName] = home_resolve_student_track($conn, $stuEmail, $stuId);
  if ($studentTrackId > 0) {
    $homeRecommendedRows = home_fetch_track_courses($conn, $studentTrackId);
  }

  if ($studentTrackName !== '') {
    $homeRecommendationSubtitle = 'Courses aligned with your selected track: ' . $studentTrackName . '.';
  }
}

include('./mainInclude/header.php');
?>

<?php if (!$isStudentLoggedIn): ?>
<section class="home-hero">
  <div class="container">
    <div class="hero-stage">
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
<?php else: ?>
<section class="home-recommendations student-home-recommendations">
  <div class="container">
    <div class="home-section-heading">
      <div>
        <span class="home-section-kicker"><?php echo htmlspecialchars($homeRecommendationKicker, ENT_QUOTES, 'UTF-8'); ?></span>
        <h2>Recommended for You</h2>
        <p><?php echo htmlspecialchars($homeRecommendationSubtitle, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
      <a href="Student/myCourse.php" class="home-feature-link home-section-link">Open My Courses</a>
    </div>

    <div class="home-recommend-grid">
      <?php if (count($homeRecommendedRows) > 0): ?>
        <?php foreach ($homeRecommendedRows as $row): ?>
          <article class="home-recommend-card">
            <div class="home-recommend-media">
              <img src="<?php echo htmlspecialchars(home_course_image_src($row), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $row['course_name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
            </div>
            <div class="home-recommend-body">
              <h3><?php echo htmlspecialchars((string) $row['course_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
              <p><?php echo htmlspecialchars((string) $row['course_desc'], ENT_QUOTES, 'UTF-8'); ?></p>
              <a href="coursedetails.php?course_id=<?php echo urlencode((string) $row['course_id']); ?>" class="home-feature-link">View Course</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <article class="home-recommend-card home-recommend-card-empty">
          <div class="home-recommend-body">
            <h3>No recommendations available yet</h3>
            <p>Your selected track does not have matching courses available right now. Browse the catalog to continue learning.</p>
            <a href="courses.php" class="home-feature-link">Browse all courses</a>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<div class="homepage-footer">
  <?php include('./mainInclude/footer.php'); ?>
</div>
