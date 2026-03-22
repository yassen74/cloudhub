<?php
include('./dbConnection.php');
include('./mainInclude/header.php');

function track_course_image_src(array $row): string
{
  $raw = isset($row['course_img']) ? trim((string)$row['course_img']) : '';
  $raw = str_replace('\\', '/', $raw);
  $file = $raw !== '' ? basename($raw) : '';

  if ($file === '') {
    return '';
  }

  $fsPath = __DIR__ . '/image/courseimg/' . $file;
  if (!is_file($fsPath)) {
    return '';
  }

  return 'image/courseimg/' . rawurlencode($file);
}

$trackId = 0;
if (isset($_GET['track_id']) && $_GET['track_id'] !== '') {
  $trackId = (int)$_GET['track_id'];
}

$tracks = [];
$resTracks = $conn->query("SELECT track_id, track_name, track_desc, track_img FROM tracks ORDER BY track_id ASC");
if ($resTracks && $resTracks->num_rows > 0) {
  while ($r = $resTracks->fetch_assoc()) {
    $tracks[] = $r;
  }
}

$track = null;
$courses = [];
if ($trackId > 0) {
  $stmtT = $conn->prepare("SELECT track_id, track_name, track_desc, track_img FROM tracks WHERE track_id = ? LIMIT 1");
  if ($stmtT) {
    $stmtT->bind_param("i", $trackId);
    $stmtT->execute();
    $resT = $stmtT->get_result();
    $track = ($resT && $resT->num_rows === 1) ? $resT->fetch_assoc() : null;
    $stmtT->close();
  }

  if ($track) {
    $stmtC = $conn->prepare("SELECT * FROM course WHERE track_id = ? ORDER BY course_id ASC");
    if ($stmtC) {
      $stmtC->bind_param("i", $trackId);
      $stmtC->execute();
      $resC = $stmtC->get_result();
      if ($resC && $resC->num_rows > 0) {
        while ($r = $resC->fetch_assoc()) {
          $courses[] = $r;
        }
      }
      $stmtC->close();
    }
  }
}
?>

<div class="container py-5 courses-page">

  <?php if ($trackId > 0 && $track): ?>
    <div class="courses-page-header courses-page-header-track">
      <span class="courses-page-eyebrow">CloudHub Track</span>
      <h1 class="courses-page-title"><?php echo htmlspecialchars($track['track_name']); ?></h1>
      <p class="courses-page-subtitle"><?php echo htmlspecialchars($track['track_desc']); ?></p>
    </div>

    <div class="courses-page-actions">
      <a class="btn btn-outline-dark btn-sm courses-back-btn" href="courses.php">
        <span class="courses-back-btn-icon" aria-hidden="true"><i class="fas fa-arrow-left"></i></span>
        <span>Back to Tracks</span>
      </a>
    </div>

    <div class="row mt-4 courses-grid track-courses-grid">
      <?php if (count($courses) > 0): ?>
        <?php foreach ($courses as $row): ?>
          <?php
            $course_id = (int)$row['course_id'];
            $courseImgSrc = track_course_image_src($row);
          ?>
          <div class="col-lg-4 col-md-6 col-sm-12 mb-4 course-item track-course-item">
            <a href="coursedetails.php?course_id=<?php echo $course_id; ?>" class="course-card-link course-card-anchor track-course-anchor">
              <div class="card course-card track-course-card">
                <div class="course-card-media">
                  <?php if ($courseImgSrc !== ''): ?>
                    <img src="<?php echo htmlspecialchars($courseImgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars((string)$row['course_name']); ?>" loading="lazy" decoding="async" />
                  <?php else: ?>
                    <div class="track-course-placeholder">
                      <span>Image Unavailable</span>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="card-body">
                  <span class="courses-track-badge">Track Course</span>
                  <h5 class="card-title"><?php echo htmlspecialchars($row['course_name']); ?></h5>
                  <p class="card-text"><?php echo htmlspecialchars($row['course_desc']); ?></p>
                </div>
                <div class="card-footer">
                  <p class="card-text course-price-line d-inline">
                    <span class="course-price-label">Price</span>
                    <small><del>&#8377 <?php echo (int)$row['course_original_price']; ?></del></small>
                    <span class="font-weight-bolder course-price-current">&#8377 <?php echo (int)$row['course_price']; ?></span>
                  </p>
                  <a class="btn btn-primary text-white font-weight-bolder float-right course-card-cta" href="coursedetails.php?course_id=<?php echo $course_id; ?>">Enroll</a>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-dark">No courses found for this track.</div>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="courses-page-header">
      <span class="courses-page-eyebrow">Learning Paths</span>
      <h1 class="courses-page-title">Tracks</h1>
      <p class="courses-page-subtitle">Choose a track to view its courses</p>
    </div>

    <div class="row mt-4 courses-grid">
      <?php if (count($tracks) > 0): ?>
        <?php foreach ($tracks as $t): ?>
          <div class="col-sm-6 col-lg-3 mb-4 course-item">
            <a href="courses.php?track_id=<?php echo (int)$t['track_id']; ?>" class="btn course-card-link course-card-anchor">
              <div class="card h-100 course-card">
<?php
$timg = '';
if (isset($t['track_img'])) { $timg = trim((string)$t['track_img']); }
?>
<?php if ($timg !== ''): ?>
  <div class="course-card-media">
    <img src="<?php echo htmlspecialchars($timg); ?>" class="card-img-top" alt="Track" loading="lazy" decoding="async">
  </div>
<?php else: ?>
  <div class="course-card-media">
    <div class="track-card-placeholder">No image</div>
  </div>
<?php endif; ?>

<div class="card-body">
                  <span class="courses-track-badge">Learning Track</span>
                  <h5 class="card-title"><?php echo htmlspecialchars($t['track_name']); ?></h5>
                  <p class="card-text"><?php echo htmlspecialchars($t['track_desc']); ?></p>
                </div>
                <div class="card-footer">
                  <span class="btn course-card-linktext">View Courses</span>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-dark">No tracks found.</div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<?php include('./mainInclude/footer.php'); ?>
