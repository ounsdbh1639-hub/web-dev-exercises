<?php
// final_project/admin/students.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role('admin');
$pdo = getPDO();

// Add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $matricule = trim($_POST['matricule']);
    $fullname  = trim($_POST['fullname']);
    $group     = trim($_POST['group_name']);

    if ($matricule && $fullname) {
        $stmt = $pdo->prepare("INSERT INTO students (matricule, fullname, group_name) VALUES (?, ?, ?)");
        $stmt->execute([$matricule, $fullname, $group]);
        header('Location: /tp_web/final_project/admin/students.php');
        exit;
    }
}

// Delete student
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: /tp_web/final_project/admin/students.php');
    exit;
}

// Fetch students
$stmt = $pdo->query("SELECT id, matricule, fullname, group_name FROM students ORDER BY fullname");
$students = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>Manage Students</h2>

  <section class="card">
    <h3>Add Student</h3>
    <form method="post" class="form-inline">
      <input type="hidden" name="action" value="add">
      <label>Matricule:<input name="matricule" required></label>
      <label>Full name:<input name="fullname" required></label>
      <label>Group:<input name="group_name"></label>
      <button type="submit">Add</button>
    </form>
  </section>

  <section class="card">
    <h3>Students List (<?php echo count($students); ?>)</h3>
    <table class="table">
      <thead><tr><th>#</th><th>Matricule</th><th>Full name</th><th>Group</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($students as $s): ?>
        <tr>
          <td><?php echo $s['id']; ?></td>
          <td><?php echo htmlspecialchars($s['matricule']); ?></td>
          <td><?php echo htmlspecialchars($s['fullname']); ?></td>
          <td><?= htmlspecialchars($s['group_name'] ?? '') ?></td>
          <td>
            <a class="btn small" href="/tp_web/final_project/admin/students.php?delete_id=<?php echo $s['id']; ?>" onclick="return confirm('Delete student?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
