<?php
// admin/import.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/db_connect.php';

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $f = $_FILES['csv_file'];
    if ($f['error'] === UPLOAD_ERR_OK) {
        $tmp = $f['tmp_name'];
        if (($handle = fopen($tmp, 'r')) !== false) {
            $row = 0;
            $pdo->beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row++;
                    if ($row === 1) {
                        // assume header: try to detect numeric matricule otherwise skip header
                        if (preg_match('/[A-Za-z]/', $data[0])) continue;
                    }
                    $matricule = trim($data[0] ?? '');
                    $fullname = trim($data[1] ?? '');
                    $group = trim($data[2] ?? '');
                    if ($matricule === '' || $fullname === '') continue;

                    // Create user if not exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
                    $stmt->execute([':u' => $matricule]);
                    if (!$stmt->fetch()) {
                        $password_hash = password_hash($matricule, PASSWORD_BCRYPT);
                        $insU = $pdo->prepare("INSERT INTO users (username, password_hash, role, fullname) VALUES (:u, :ph, 'student', :fn)");
                        $insU->execute([':u' => $matricule, ':ph' => $password_hash, ':fn' => $fullname]);
                        $userId = $pdo->lastInsertId();
                    } else {
                        $userId = $stmt->fetchColumn();
                    }

                    // Insert student row if not exists
                    $stmtS = $pdo->prepare("SELECT id FROM students WHERE matricule = :m LIMIT 1");
                    $stmtS->execute([':m' => $matricule]);
                    if (!$stmtS->fetch()) {
                        $insS = $pdo->prepare("INSERT INTO students (fullname, group_name, matricule, group_id, user_id) VALUES (:fn, :gn, :mat, :gid, :uid)");
                        $insS->execute([':fn' => $fullname, ':gn' => $group, ':mat' => $matricule, ':gid' => $group, ':uid' => $userId]);
                    }
                }
                $pdo->commit();
                $messages[] = "Import completed.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $messages[] = "Error during import.";
                error_log($e->getMessage());
            }
            fclose($handle);
        } else {
            $messages[] = "Unable to open uploaded file.";
        }
    } else {
        $messages[] = "File upload error.";
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row">
  <div class="col-md-8">
    <h3>Import Students</h3>
    <?php foreach ($messages as $m): ?>
        <div class="alert alert-info"><?= htmlspecialchars($m) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">CSV file (matricule,fullname,group)</label>
        <input type="file" name="csv_file" accept=".csv" class="form-control">
      </div>
      <button class="btn btn-primary">Import</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>

