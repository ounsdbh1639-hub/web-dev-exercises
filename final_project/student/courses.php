<?php
// final_project/student/courses.php
require_once __DIR__ . '/../includes/auth.php';
require_login();

if (!in_array($_SESSION['user']['role'], ['student', 'admin', 'professor'])) {
    http_response_code(403);
    echo "Forbidden - insufficient role.";
    exit;
}

$pdo = getPDO();
$user = current_user();

// 1) Find student record linked by matricule = username
$stmt = $pdo->prepare("
    SELECT s.id, s.fullname, g.name AS group_name
    FROM students s
    JOIN groups_tbl g ON s.group_id = g.id
    WHERE s.matricule = ?
");
$stmt->execute([$user['username']]);
$student = $stmt->fetch();

if (!$student) {
    echo "No student record linked to your user account.";
    exit;
}

// 2) Fetch courses for student's group
$stmt2 = $pdo->prepare("
    SELECT c.id, c.code, c.title, cg.id AS course_group_id
    FROM course_groups cg
    JOIN courses c ON cg.course_id = c.id
    JOIN groups_tbl g ON cg.group_id = g.id
    WHERE g.name = ?
");
$stmt2->execute([$student['group_name']]);
$courses = $stmt2->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>Welcome, <?php echo htmlspecialchars($student['fullname']); ?></h2>
  <p>Your group: <strong><?php echo htmlspecialchars($student['group_name']); ?></strong></p>

  <section class="card">
    <h3>Enrolled Courses</h3>
    <?php if (count($courses) === 0) echo "<p>No courses found for your group.</p>"; ?>
    <ul>
      <?php foreach ($courses as $c): ?>
        <li>
          <?php echo htmlspecialchars($c['code'] . " — " . $c['title']); ?>
          <a class="btn small" href="/tp_web/final_project/student/attendance.php?cg=<?php echo $c['course_group_id']; ?>">View attendance</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

