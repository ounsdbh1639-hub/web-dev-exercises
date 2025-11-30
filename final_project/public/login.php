<?php
// public/login.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $errors[] = "Username and password are required.";
    } else {
        if (login_user($username, $password)) {
            header('Location: ' . BASE_URL . 'public/index.php');
            exit;
        } else {
            $errors[] = "Invalid credentials.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4 class="card-title mb-3">Login</h4>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control">
          </div>
          <button class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
