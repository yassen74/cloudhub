<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$__cid = $_GET['course_id'] ?? $_GET['courseid'] ?? $_GET['cid'] ?? '';
if ($__cid !== '') { $_SESSION['last_course_id_viewed'] = $__cid; }

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$course = null;
$lessons = [];
$lessonCount = 0;

include('./dbConnection.php');

if ($course_id > 0) {
  $courseStmt = $conn->prepare("SELECT * FROM course WHERE course_id = ? LIMIT 1");
  if ($courseStmt) {
    $courseStmt->bind_param("i", $course_id);
    $courseStmt->execute();
    $courseResult = $courseStmt->get_result();
    if ($courseResult && $courseResult->num_rows > 0) {
      $course = $courseResult->fetch_assoc();
      $_SESSION['course_id'] = $course_id;
    }
    $courseStmt->close();
  }

  $lessonStmt = $conn->prepare("SELECT lesson_id, lesson_name, lesson_desc FROM lesson WHERE course_id = ? ORDER BY lesson_id ASC");
  if ($lessonStmt) {
    $lessonStmt->bind_param("i", $course_id);
    $lessonStmt->execute();
    $lessonResult = $lessonStmt->get_result();
    if ($lessonResult) {
      while ($lessonRow = $lessonResult->fetch_assoc()) {
        $lessons[] = $lessonRow;
      }
      $lessonCount = count($lessons);
    }
    $lessonStmt->close();
  }
}

// Header Include from mainInclude
include('./mainInclude/header.php');
?>  
    <div class="container-fluid course-details-hero"> <!-- Start Course Page Banner -->
      <div class="row">
        <img src="./image/coursebanner.jpg" alt="courses" class="course-details-banner"/>
      </div> 
    </div> <!-- End Course Page Banner -->

    <main class="course-details-page">
    <div class="container course-details-shell"> <!-- Start All Course -->
      <?php if ($course): ?>
        <?php
          $courseIdForCheckout = urlencode((string)$course['course_id']);
          $courseImage = str_replace('..', '.', (string)$course['course_img']);
          $courseName = htmlspecialchars((string)$course['course_name'], ENT_QUOTES, 'UTF-8');
          $courseDesc = htmlspecialchars((string)$course['course_desc'], ENT_QUOTES, 'UTF-8');
          $courseDuration = htmlspecialchars((string)$course['course_duration'], ENT_QUOTES, 'UTF-8');
          $coursePrice = htmlspecialchars((string)$course['course_price'], ENT_QUOTES, 'UTF-8');
          $courseOriginalPrice = htmlspecialchars((string)$course['course_original_price'], ENT_QUOTES, 'UTF-8');
        ?>
        <section class="course-details-surface" aria-labelledby="course-title">
          <div class="course-details-card">
          <div class="row align-items-center">
            <div class="col-lg-5 col-md-5">
              <div class="course-details-media">
                <div class="course-image-frame">
                  <img
                    src="<?php echo $courseImage !== '' ? $courseImage : './image/coursebanner.jpg'; ?>"
                    class="course-details-image"
                    alt="<?php echo $courseName; ?>"
                    onerror="this.onerror=null;this.src='./image/coursebanner.jpg';"
                  />
                </div>
                <div class="course-media-caption">
                  <span class="course-media-caption-label">Included</span>
                  <strong><?php echo $lessonCount; ?> module<?php echo $lessonCount === 1 ? '' : 's'; ?></strong>
                  <span class="course-media-divider" aria-hidden="true"></span>
                  <span>Structured guided learning</span>
                </div>
              </div>
            </div>
            <div class="col-lg-7 col-md-7">
              <div class="course-details-panel">
                <span class="course-details-kicker">Course Overview</span>
                <h1 class="course-details-title" id="course-title"><?php echo $courseName; ?></h1>
                <p class="course-details-copy">
                  <span class="course-details-label">Description</span>
                  <?php echo $courseDesc; ?>
                </p>

                <div class="course-details-highlights" aria-label="Course quick facts">
                  <article class="course-highlight-card">
                    <span class="course-details-label">Duration</span>
                    <strong><?php echo $courseDuration; ?></strong>
                  </article>
                  <article class="course-highlight-card">
                    <span class="course-details-label">Modules</span>
                    <strong><?php echo $lessonCount; ?></strong>
                  </article>
                  <article class="course-highlight-card">
                    <span class="course-details-label">Learning Path</span>
                    <strong>Guided and practical</strong>
                  </article>
                </div>

                <form action="checkout.php?course_id=<?php echo $courseIdForCheckout; ?>" method="post" class="course-details-form">
                  <div class="course-price-block">
                    <span class="course-details-label">Price</span>
                    <p class="course-price-row">
                      <small class="course-price-original"><del>&#8377 <?php echo $courseOriginalPrice; ?></del></small>
                      <span class="course-price-current">&#8377 <?php echo $coursePrice; ?></span>
                    </p>
                    <span class="course-price-note">Full course access with all listed modules.</span>
                  </div>
                  <input type="hidden" name="id" value="<?php echo $coursePrice; ?>">
                  <button type="submit" class="btn course-buy-btn" name="buy">Enroll Now</button>
                </form>
              </div>
            </div>
          </div>
          </div>
        </section>
      <?php else: ?>
        <section class="course-details-surface" aria-live="polite">
          <div class="course-details-card course-empty-state">
            <span class="course-details-kicker">Course Details</span>
            <h1 class="course-details-title">Course not found</h1>
            <p class="course-details-copy">We could not load this course right now. Please return to the courses page and try again.</p>
            <a class="btn course-buy-btn" href="courses.php">Browse Courses</a>
          </div>
        </section>
      <?php endif; ?>

      <section class="course-details-surface course-lessons-surface">
      <div class="course-lessons-card">
        <div class="course-lessons-header">
          <div>
            <span class="course-details-kicker course-lessons-kicker">Learning Journey</span>
            <h2 class="course-lessons-title">Modules and lessons</h2>
            <p class="course-lessons-subtitle">A clearer breakdown of what you will learn in this course.</p>
          </div>
          <div class="course-lessons-summary" aria-label="Modules count">
            <strong><?php echo $lessonCount; ?></strong>
            <span>Total modules</span>
          </div>
        </div>

        <?php if ($lessonCount > 0): ?>
          <div class="table-responsive course-lessons-table-wrap">
            <table class="table course-lessons-table">
              <thead>
                <tr>
                  <th scope="col">Module</th>
                  <th scope="col">Lesson</th>
                  <th scope="col">Learning focus</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lessons as $index => $lesson): ?>
                  <?php
                    $moduleNumber = $index + 1;
                    $lessonName = htmlspecialchars((string)$lesson['lesson_name'], ENT_QUOTES, 'UTF-8');
                    $lessonDescRaw = trim((string)($lesson['lesson_desc'] ?? ''));
                    $lessonDesc = $lessonDescRaw !== ''
                      ? htmlspecialchars($lessonDescRaw, ENT_QUOTES, 'UTF-8')
                      : 'Core concepts, walkthroughs, and practical exercises for this module.';
                  ?>
                  <tr>
                    <th scope="row" data-label="Module">
                      <span class="course-module-badge">Module <?php echo $moduleNumber; ?></span>
                    </th>
                    <td data-label="Lesson">
                      <div class="course-lesson-name"><?php echo $lessonName; ?></div>
                    </td>
                    <td data-label="Learning focus">
                      <div class="course-lesson-desc"><?php echo $lessonDesc; ?></div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="course-lessons-empty" role="status">
            No modules are available for this course yet.
          </div>
        <?php endif; ?>
      </div>
      </section>
      </div>
      </main>
     <?php 
  // Footer Include from mainInclude 
  include('./mainInclude/footer.php'); 
?>  
