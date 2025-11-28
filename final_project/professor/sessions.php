<?php
// final_project/professor/sessions.php
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

// Fetch course_groups where professor_id = current user
$stmt = $pdo->prepare("SELECT cg.id AS cgid, c.title AS course, g.name AS groupname
                       FROM course_groups cg
                       JOIN courses c ON cg.course_id = c.id
                       JOIN groups_tbl g ON cg.group_id = g.id
                       WHERE cg.professor_id = ?");
$stmt->execute([$user['id']]);
$groups = $stmt->fetchAll();

// Create session for selected course_group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_group_id'])) {
    $cgid = (int)$_POST['course_group_id'];
    $today = date('Y-m-d');
    // prevent duplicate session for same course_group and date
    $stmtC = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE course_group_id = ? AND date = ?");
    $stmtC->execute([$cgid, $today]);
    if ($stmtC->fetchColumn() == 0) {
        $stmtIns = $pdo->prepare("INSERT INTO attendance_sessions (course_group_id, date, opened_by, status) VALUES (?, ?, ?, 'open')");
        $stmtIns->execute([$cgid, $today, $user['id']]);
        $session_id = $pdo->lastInsertId();
        header("Location: /tp_web/final_project/professor/session.php?session_id={$session_id}");
        exit;
    } else {
        $error = "A session for today already exists for that course/group.";
    }
}

// fetch all sessions by this professor
$stmt2 = $pdo->prepare("SELECT s.id, s.date, s.status, c.title, g.name
                       FROM attendance_sessions s
                       JOIN course_groups cg ON s.course_group_id = cg.id
                       JOIN courses c ON cg.course_id = c.id
                       JOIN groups_tbl g ON cg.group_id = g.id
                       WHERE cg.professor_id = ?
                       ORDER BY s.date DESC");
$stmt2->execute([$user['id']]);
$sessions = $stmt2->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>My Course Groups & Sessions</h2>

  <section class="card">
    <h3>Create new session</h3>
    <?php if (!empty($error)) echo "<p class='notice'>".htmlspecialchars($error)."</p>"; ?>
    <form method="post" class="form-inline">
      <label>Course group:
        <select name="course_group_id" required>
          <option value="">-- choose --</option>
          <?php foreach ($groups as $g): ?>
            <option value="<?php echo $g['cgid']; ?>"><?php echo htmlspecialchars($g['course'] . ' — ' . $g['groupname']); ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit">Open Session (today)</button>
    </form>
  </section>

  <section class="card">
    <h3>Recent Sessions</h3>
    <table class="table">
      <thead><tr><th>#</th><th>Date</th><th>Course</th><th>Group</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($sessions as $s): ?>
        <tr>
          <td><?php echo $s['id']; ?></td>
          <td><?php echo $s['date']; ?></td>
          <td><?php echo htmlspecialchars($s['title']); ?></td>
          <td><?php echo htmlspecialchars($s['name']); ?></td>
          <td><?php echo $s['status']; ?></td>
          <td>
            <a class="btn small" href="/tp_web/final_project/professor/session.php?session_id=<?php echo $s['id']; ?>">Open</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
