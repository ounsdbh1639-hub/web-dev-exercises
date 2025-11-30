<?php
// professor/sessions.php
require_once __DIR__ . '/../includes/auth.php';
require_role('professor');
require_once __DIR__ . '/../includes/db_connect.php';

$prof = current_user();
$messages = [];

// create session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $group_id = trim($_POST['group_id'] ?? 'all');
    $date = $_POST['date'] ?? date('Y-m-d');
    if ($course_id <= 0) {
        $messages[] = "Course required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (:course, :group, :date, :opened_by, 'open')");
        $stmt->execute([':course' => $course_id, ':group' => $group_id, ':date' => $date, ':opened_by' => $prof['id']]);
        $messages[] = "Session opened.";
    }
}

// close session
if (isset($_GET['close'])) {
    $id = (int)$_GET['close'];
    $pdo->prepare("UPDATE attendance_sessions SET status='closed' WHERE id=:id")->execute([':id' => $id]);
    $messages[] = "Session closed.";
}

// list sessions
$sessions = $pdo->query("SELECT s.*, c.title as course_title, u.fullname as opened_by_name FROM attendance_sessions s LEFT JOIN courses c ON s.course_id=c.id LEFT JOIN users u ON s.opened_by=u.id ORDER BY s.date DESC")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses ORDER BY title")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-md-8">
    <h3>Your Sessions</h3>
    <?php foreach ($messages as $m): ?><div class="alert alert-info"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
    <table class="table">
  <thead>
    <tr>
      <th>#</th>
      <th>Course</th>
      <th>Date</th>
      <th>Group</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($sessions as $s): ?>
      <tr>
        <td><?= (int)$s['id'] ?></td>
        <td><?= htmlspecialchars($s['course_title'] ?? 'Unknown Course') ?></td>
        <td><?= htmlspecialchars($s['date'] ?? '-') ?></td>
        <td><?= htmlspecialchars($s['group_id'] ?? '-') ?></td>
        <td><?= htmlspecialchars($s['status'] ?? '-') ?></td>
        <td>
          <a class="btn btn-sm btn-primary"
             href="<?= BASE_URL ?>professor/session.php?id=<?= (int)$s['id'] ?>">
             Open
          </a>

          <?php if (($s['status'] ?? '') === 'open'): ?>
            <a class="btn btn-sm btn-warning"
               href="?close=<?= (int)$s['id'] ?>">
               Close
            </a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  </div>

  <div class="col-md-4">
    <h5>Create Session</h5>
    <form method="post">
      <div class="mb-2">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select">
          <option value="">Select course</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">Group</label>
        <input name="group_id" value="all" class="form-control">
      </div>
      <div class="mb-2">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <button name="create_session" class="btn btn-success">Create</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
