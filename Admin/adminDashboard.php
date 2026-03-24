<?php
require_once __DIR__ . '/adminInclude/session.php';

if (!isset($_SESSION['admin_email'])) {
  header('Location: index.php');
  exit();
}

define('TITLE', 'Dashboard');
define('PAGE', 'dashboard');

include('../dbConnection.php');
include('./adminInclude/header.php');

$adminEmail = $_SESSION['admin_email'];

$totalcourse = 0;
$totalstu = 0;
$totalsold = 0;

if ($r = $conn->query("SELECT COUNT(*) AS c FROM course")) {
  $totalcourse = (int)($r->fetch_assoc()['c'] ?? 0);
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM student")) {
  $totalstu = (int)($r->fetch_assoc()['c'] ?? 0);
}
if ($r = $conn->query("SELECT COUNT(*) AS c FROM courseorder")) {
  $totalsold = (int)($r->fetch_assoc()['c'] ?? 0);
}

if (isset($_REQUEST['delete'])) {
  $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
  if ($id > 0) {
    $del = $conn->prepare("DELETE FROM courseorder WHERE order_id = ? LIMIT 1");
    if ($del) {
      $del->bind_param("i", $id);
      $del->execute();
      $del->close();
      echo '<meta http-equiv="refresh" content="0;URL=?deleted" />';
      exit;
    }
  }
}
?>
  <div class="col-sm-9 mt-5">
    <div class="row mx-5 text-center">
      <div class="col-sm-4 mt-5">
        <div class="card text-white bg-danger mb-3" style="max-width: 18rem;">
          <div class="card-header">Courses</div>
          <div class="card-body">
            <h4 class="card-title"><?php echo $totalcourse; ?></h4>
            <a class="btn text-white" href="courses.php">View</a>
          </div>
        </div>
      </div>
      <div class="col-sm-4 mt-5">
        <div class="card text-white bg-success mb-3" style="max-width: 18rem;">
          <div class="card-header">Students</div>
          <div class="card-body">
            <h4 class="card-title"><?php echo $totalstu; ?></h4>
            <a class="btn text-white" href="students.php">View</a>
          </div>
        </div>
      </div>
      <div class="col-sm-4 mt-5">
        <div class="card text-white bg-info mb-3" style="max-width: 18rem;">
          <div class="card-header">Sold</div>
          <div class="card-body">
            <h4 class="card-title"><?php echo $totalsold; ?></h4>
            <a class="btn text-white" href="sellReport.php">View</a>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-5 mt-5 text-center">
      <p class="bg-dark text-white p-2">Course Ordered</p>
      <?php
      $sql = "
        SELECT
          o.order_id,
          o.course_id,
          o.order_date,
          o.order_amount,
          s.stu_email
        FROM courseorder o
        LEFT JOIN student s ON s.stu_id = o.stu_id
        ORDER BY o.order_date DESC, o.order_id DESC
      ";
      $result = $conn->query($sql);
      if ($result && $result->num_rows > 0) {
        echo '<table class="table">
          <thead>
          <tr>
            <th scope="col">Order ID</th>
            <th scope="col">Course ID</th>
            <th scope="col">Student Email</th>
            <th scope="col">Order Date</th>
            <th scope="col">Amount</th>
            <th scope="col">Action</th>
          </tr>
          </thead>
          <tbody>';
        while ($row = $result->fetch_assoc()) {
          echo '<tr>';
          echo '<th scope="row">' . (int)($row["order_id"] ?? 0) . '</th>';
          echo '<td>' . (int)($row["course_id"] ?? 0) . '</td>';
          echo '<td>' . htmlspecialchars((string)($row["stu_email"] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
          echo '<td>' . htmlspecialchars((string)($row["order_date"] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
          echo '<td>' . htmlspecialchars((string)($row["order_amount"] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
          echo '<td><form action="" method="POST" class="d-inline"><input type="hidden" name="id" value=' . (int)($row["order_id"] ?? 0) . '><button type="submit" class="btn btn-secondary" name="delete" value="Delete"><i class="far fa-trash-alt"></i></button></form></td>';
          echo '</tr>';
        }
        echo '</tbody></table>';
      } else {
        echo "0 Result";
      }
      ?>
    </div>
  </div>
  </div>
  </div>
<?php include('./adminInclude/footer.php'); ?>
