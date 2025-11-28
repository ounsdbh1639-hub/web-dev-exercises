<?php
// final_project/admin/import.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role('admin');
$pdo = getPDO();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $f = $_FILES['csvfile'];
    if ($f['error'] === 0 && is_uploaded_file($f['tmp_name'])) {
        $fh = fopen($f['tmp_name'], 'r');
        if ($fh !== false) {
            $count = 0;
            while (($row = fgetcsv($fh)) !== false) {
                // Expecting: matricule, fullname, group_name
                $matricule = trim($row[0] ?? '');
                $fullname  = trim($row[1] ?? '');
                $group     = trim($row[2] ?? '');
                if ($matricule && $fullname) {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO students (matricule, fullname, group_name) VALUES (?, ?, ?)");
                    $stmt->execute([$matricule, $fullname, $group]);
                    $count++;
                }
            }
            fclose($fh);
            $message = "$count students imported.";
        } else {
            $message = "Unable to read the uploaded file.";
        }
    } else {
        $message = "Upload error.";
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="container">
  <h2>Import Students (CSV)</h2>
  <?php if ($message): ?><p class="notice"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="form-inline">
    <label>CSV file: <input type="file" name="csvfile" accept=".csv" required></label>
    <button type="submit">Import</button>
  </form>
  <p>CSV format: <code>matricule,fullname,group_name</code></p>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
