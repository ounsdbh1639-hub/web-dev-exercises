<?php
// student/courses.php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
require_once __DIR__ . '/../includes/db_connect.php';

$user = current_user();
// find student row
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $user['id']]);
$student = $stmt->fetch();

// Fetch course groups matching student's group_name (best-effort)
$courses = [];
if ($student) {
    $group = $student['group_name'];
    if ($group !== '') {
        $stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN course_groups cg ON cg.course_id = c.id WHERE cg.group_id = :g");
        $stmt->execute([':g' => $group]);
        $courses = $stmt->fetchAll();
    }
    // fallback: show all courses
    if (empty($courses)) {
        $courses = $pdo->query("SELECT * FROM courses")->fetchAll();
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-md-8">
    <h3>Your Courses</h3>
    <table class="table">
      <thead><tr><th>#</th><th>Course</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><a class="btn btn-sm btn-primary" href="<?= BASE_URL ?>student/attendance.php?course_id=<?= (int)$c['id'] ?>">View Attendance</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

