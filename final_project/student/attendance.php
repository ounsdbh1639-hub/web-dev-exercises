<?php
// final_project/student/attendance.php
require_once __DIR__ . '/../includes/auth.php';

require_login();
if (!in_array($_SESSION['user']['role'], ['admin', 'professor', 'student'])) {
    http_response_code(403);
    echo "Forbidden - insufficient role.";
    exit;
}
$pdo = getPDO();
$user = current_user();

// find student record linked to user
$stmt = $pdo->prepare("SELECT id, fullname, group_name FROM students WHERE user_id = ?");
$stmt->execute([$user['id']]);
$student = $stmt->fetch();
if (!$student) { echo "No student record found."; exit; }

$student_id = $student['id'];
// Optionally filter by course_group
$cg = isset($_GET['cg']) ? (int)$_GET['cg'] : null;

$sql = "SELECT a.*, s.date, c.title, g.name AS groupname
        FROM attendance a
        JOIN attendance_sessions s ON a.session_id = s.id
        JOIN course_groups cg ON s.course_group_id = cg.id
        JOIN courses c ON cg.course_id = c.id
        JOIN groups_tbl g ON cg.group_id = g.id
        WHERE a.student_id = ?";
$params = [$student_id];
if ($cg) {
    $sql .= " AND cg.id = ?";
    $params[] = $cg;
}
$sql .= " ORDER BY s.date DESC";

$stmt2 = $pdo->prepare($sql);
$stmt2->execute($params);
$records = $stmt2->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>Attendance for <?php echo htmlspecialchars($student['fullname']); ?></h2>
  <?php if (count($records) === 0) echo "<p>No attendance records found.</p>"; ?>
  <table class="table">
    <thead><tr><th>Date</th><th>Course</th><th>Group</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($records as $r): ?>
        <tr>
          <td><?php echo $r['date']; ?></td>
          <td><?php echo htmlspecialchars($r['title']); ?></td>
          <td><?php echo htmlspecialchars($r['groupname']); ?></td>
          <td><?php echo htmlspecialchars($r['status']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
