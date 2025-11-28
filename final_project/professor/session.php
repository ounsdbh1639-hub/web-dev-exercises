<?php
// final_project/professor/session.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_login();
if (!in_array($_SESSION['user']['role'], ['admin', 'professor'])) {
    http_response_code(403);
    echo "Forbidden - insufficient role.";
    exit;
}

$pdo = getPDO();
$user = current_user();

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if (!$session_id) {
    echo "Session id required.";
    exit;
}

// load session and course_group
$stmt = $pdo->prepare("SELECT s.*, cg.course_id, cg.group_id, c.title, g.name AS groupname
                       FROM attendance_sessions s
                       JOIN course_groups cg ON s.course_group_id = cg.id
                       JOIN courses c ON cg.course_id = c.id
                       JOIN groups_tbl g ON cg.group_id = g.id
                       WHERE s.id = ?");
$stmt->execute([$session_id]);
$session = $stmt->fetch();
if (!$session) { echo "Session not found."; exit; }

// verify professor owns the course_group
$stmtChk = $pdo->prepare("SELECT professor_id FROM course_groups WHERE id = ?");
$stmtChk->execute([$session['course_group_id']]);
$cg = $stmtChk->fetch();
if ($cg['professor_id'] != $user['id']) { echo "Forbidden."; exit; }

// load students in the group (by group_name matching groups_tbl.name)
$stmtS = $pdo->prepare("SELECT id, matricule, fullname FROM students WHERE group_name = ? ORDER BY fullname");
$stmtS->execute([$session['groupname']]);
$students = $stmtS->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    // insert attendance records
    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("INSERT INTO attendance (student_id, session_id, status) VALUES (?, ?, ?)");
        foreach ($_POST['status'] as $student_id => $status) {
            $ins->execute([(int)$student_id, $session_id, $status === 'present' ? 'present' : 'absent']);
        }
        // mark session closed
        $upd = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
        $upd->execute([$session_id]);
        $pdo->commit();
        $message = "Attendance recorded and session closed.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>Session - <?php echo htmlspecialchars($session['title'] . " (" . $session['groupname'] . ")"); ?> — <?php echo $session['date']; ?></h2>
  <?php if (!empty($message)) echo "<p class='notice'>".htmlspecialchars($message)."</p>"; ?>
  <?php if (!empty($error)) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>
  <?php if ($session['status'] === 'closed'): ?>
    <p>This session is closed. Attendance already recorded.</p>
    <a href="/tp_web/final_project/professor/sessions.php">Back to sessions</a>
  <?php else: ?>
    <form method="post">
      <table class="table">
        <thead><tr><th>Student</th><th>Present</th><th>Absent</th></tr></thead>
        <tbody>
        <?php foreach ($students as $st): ?>
          <tr>
            <td><?php echo htmlspecialchars($st['fullname'] . " (" . $st['matricule'] . ")"); ?></td>
            <td><input type="radio" name="status[<?php echo $st['id']; ?>]" value="present" required></td>
            <td><input type="radio" name="status[<?php echo $st['id']; ?>]" value="absent"></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <button type="submit">Save Attendance & Close Session</button>
    </form>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
