<?php
// student/attendance.php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
require_once __DIR__ . '/../includes/db_connect.php';

$user = current_user();
$course_id = (int)($_GET['course_id'] ?? 0);

// find student id
$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :uid LIMIT 1");
$stmt->execute([':uid' => $user['id']]);
$student = $stmt->fetch();
if (!$student) {
    die("Student profile not found.");
}

// submit justification
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id']) && isset($_POST['reason'])) {
    $session_id = (int)$_POST['session_id'];
    $reason = trim($_POST['reason']);
    $file_path = null;
    if (!empty($_FILES['attachment']['tmp_name'])) {
        $u = $_FILES['attachment'];
        if ($u['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($u['name'], PATHINFO_EXTENSION);
            $filename = 'just_' . $student['id'] . '_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../public/uploads/' . $filename;
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
            move_uploaded_file($u['tmp_name'], $dest);
            $file_path = 'public/uploads/' . $filename;
        }
    }
    $ins = $pdo->prepare("INSERT INTO justifications (student_id, session_id, reason, file_path, status) VALUES (:sid, :sess, :r, :fp, 'pending')");
    $ins->execute([':sid' => $student['id'], ':sess' => $session_id, ':r' => $reason, ':fp' => $file_path]);
    $messages[] = "Justification submitted.";
}

// fetch attendance records for the course
$rows = $pdo->prepare("SELECT s.id as session_id, s.date, s.group_id, a.status FROM attendance_sessions s LEFT JOIN attendance a ON a.session_id = s.id AND a.student_id = :stud WHERE s.course_id = :course ORDER BY s.date DESC");
$rows->execute([':stud' => $student['id'], ':course' => $course_id]);
$records = $rows->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-md-8">
    <h3>Attendance for Course #<?= (int)$course_id ?></h3>
    <?php foreach ($messages as $m): ?><div class="alert alert-info"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
    <table class="table">
      <thead><tr><th>Date</th><th>Group</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($records as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td><?= htmlspecialchars($r['group_id']) ?></td>
            <td><?= htmlspecialchars($r['status'] ?? 'not recorded') ?></td>
            <td>
              <?php if (($r['status'] ?? '') === 'absent'): ?>
                <button class="btn btn-sm btn-outline-primary justify-btn" data-session="<?= (int)$r['session_id'] ?>">Justify</button>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal for justification -->
<div class="modal" tabindex="-1" id="justifyModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title">Submit Justification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="session_id" id="modal_session_id">
          <div class="mb-2"><label class="form-label">Reason</label><textarea name="reason" class="form-control" required></textarea></div>
          <div class="mb-2"><label class="form-label">Attachment (optional)</label><input type="file" name="attachment" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Submit</button></div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  $('.justify-btn').on('click', function(){
    var sid = $(this).data('session');
    $('#modal_session_id').val(sid);
    var modal = new bootstrap.Modal(document.getElementById('justifyModal'));
    modal.show();
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

