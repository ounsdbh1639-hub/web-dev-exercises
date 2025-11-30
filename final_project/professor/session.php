<?php
// professor/session.php
require_once __DIR__ . '/../includes/auth.php';
require_role('professor');
require_once __DIR__ . '/../includes/db_connect.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid session id");
}

// fetch session
$session = $pdo->prepare("SELECT s.*, c.title as course_title FROM attendance_sessions s LEFT JOIN courses c ON s.course_id=c.id WHERE s.id=:id LIMIT 1");
$session->execute([':id' => $id]);
$session = $session->fetch();
if (!$session) die("Session not found.");

// fetch students of that group (simple selection by students table)
$group = $session['group_id'];
if ($group === 'all' || $group === '') {
    $students = $pdo->query("SELECT * FROM students ORDER BY fullname")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE group_name = :g ORDER BY fullname");
    $stmt->execute([':g' => $group]);
    $students = $stmt->fetchAll();
}

// handle attendance update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $pdo->beginTransaction();
    try {
        // remove existing for session to simplify logic
        $pdo->prepare("DELETE FROM attendance WHERE session_id = :sid")->execute([':sid' => $id]);
        $attendance = $_POST['attendance']; // array student_id => status
        $ins = $pdo->prepare("INSERT INTO attendance (student_id, session_id, status) VALUES (:sid, :sess, :st)");
        foreach ($attendance as $studentId => $status) {
            $ins->execute([':sid' => (int)$studentId, ':sess' => $id, ':st' => $status]);
        }
        $pdo->commit();
        $message = "Attendance recorded.";
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log($e->getMessage());
        $message = "Error saving attendance.";
    }
}

// load existing attendance map
$rows = $pdo->prepare("SELECT * FROM attendance WHERE session_id = :sid");
$rows->execute([':sid' => $id]);
$existing = [];
foreach ($rows->fetchAll() as $r) {
    $existing[$r['student_id']] = $r['status'];
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-12">
    <h3>Session: <?= htmlspecialchars($session['course_title']) ?> — <?= htmlspecialchars($session['date']) ?></h3>
    <?php if (!empty($message)): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post">
      <table class="table table-hover">
        <thead><tr><th>#</th><th>Name</th><th>Matricule</th><th>Attendance</th></tr></thead>
        <tbody>
          <?php foreach ($students as $i => $s): $sid = (int)$s['id']; ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($s['fullname']) ?></td>
              <td><?= htmlspecialchars($s['matricule']) ?></td>
              <td>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="attendance[<?= $sid ?>]" value="present" id="p-<?= $sid ?>" <?= (isset($existing[$sid]) && $existing[$sid]==='present') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="p-<?= $sid ?>">Present</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="attendance[<?= $sid ?>]" value="absent" id="a-<?= $sid ?>" <?= (isset($existing[$sid]) && $existing[$sid]==='absent') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="a-<?= $sid ?>">Absent</label>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button class="btn btn-primary">Save Attendance</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

