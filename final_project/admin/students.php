<?php
// admin/students.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db_connect.php';

$errors = [];
$success = null;

// Add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullname = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_name = trim($_POST['group_name'] ?? '');
    if ($fullname === '' || $matricule === '') {
        $errors[] = "Fullname and matricule required.";
    } else {
        // create user account for student as well
        $pdo->beginTransaction();
        try {
            // create users row
            $password_hash = password_hash($matricule, PASSWORD_BCRYPT);
            $stmtUser = $pdo->prepare("INSERT INTO users (username, password_hash, role, fullname) VALUES (:u, :ph, 'student', :fn)");
            $stmtUser->execute([':u' => $matricule, ':ph' => $password_hash, ':fn' => $fullname]);
            $userId = $pdo->lastInsertId();

            // create students row
            $stmt = $pdo->prepare("INSERT INTO students (fullname, group_name, matricule, group_id, user_id) VALUES (:fn, :gn, :mat, :gid, :uid)");
            $stmt->execute([
                ':fn' => $fullname,
                ':gn' => $group_name,
                ':mat' => $matricule,
                ':gid' => $group_name,
                ':uid' => $userId
            ]);
            $pdo->commit();
            $success = "Student added.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Error adding student.";
            error_log($e->getMessage());
        }
    }
}

// Delete student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE s, u FROM students s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = :id");
        $stmt->execute([':id' => $id]);
        // fallback single delete if DB doesn't support multi-table delete
        if ($stmt->rowCount() === 0) {
            $pdo->prepare("DELETE FROM students WHERE id = :id")->execute([':id' => $id]);
        }
        $success = "Student removed.";
    } catch (Exception $e) {
        $errors[] = "Error deleting student.";
    }
}

// Fetch students
$students = $pdo->query("SELECT s.*, u.username FROM students s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.id DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-md-8">
    <h3>Students</h3>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div><?php endif; ?>

    <table class="table table-striped">
      <thead><tr><th>#</th><th>Fullname</th><th>Matricule</th><th>Group</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= (int)$s['id'] ?></td>
            <td><?= htmlspecialchars($s['fullname']) ?></td>
            <td><?= htmlspecialchars($s['matricule']) ?></td>
            <td><?= htmlspecialchars($s['group_name']) ?></td>
            <td>
              <a class="btn btn-sm btn-danger" href="?delete=<?= (int)$s['id'] ?>" onclick="return confirm('Delete student?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="col-md-4">
    <h5>Add Student</h5>
    <form method="post">
      <input type="hidden" name="action" value="add">
      <div class="mb-2">
        <label class="form-label">Fullname</label>
        <input name="fullname" class="form-control">
      </div>
      <div class="mb-2">
        <label class="form-label">Matricule (will be used as username & initial password)</label>
        <input name="matricule" class="form-control">
      </div>
      <div class="mb-2">
        <label class="form-label">Group</label>
        <input name="group_name" class="form-control">
      </div>
      <button class="btn btn-success">Add</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
