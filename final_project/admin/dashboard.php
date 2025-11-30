<?php
// admin/dashboard.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db_connect.php';
include __DIR__ . '/../includes/header.php';

// Fetch simple stats
$stats = [];
$statsQuery = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM users WHERE role='student') AS students,
    (SELECT COUNT(*) FROM users WHERE role='professor') AS professors,
    (SELECT COUNT(*) FROM attendance_sessions) AS sessions");
$stats = $statsQuery->fetch();
?>
<div class="row">
  <div class="col-md-8">
    <h3>Admin Dashboard</h3>
    <p>Welcome, <?= htmlspecialchars(current_user()['fullname']) ?></p>
    <div class="row">
      <div class="col-sm-4 mb-3">
        <div class="card p-3">
          <h5>Students</h5>
          <h2><?= (int)$stats['students'] ?></h2>
        </div>
      </div>
      <div class="col-sm-4 mb-3">
        <div class="card p-3">
          <h5>Professors</h5>
          <h2><?= (int)$stats['professors'] ?></h2>
        </div>
      </div>
      <div class="col-sm-4 mb-3">
        <div class="card p-3">
          <h5>Sessions</h5>
          <h2><?= (int)$stats['sessions'] ?></h2>
        </div>
      </div>
    </div>
    <p><a href="students.php" class="btn btn-outline-primary">Manage Students</a>
    <a href="import.php" class="btn btn-outline-secondary">Import Students (Excel/CSV)</a></p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

