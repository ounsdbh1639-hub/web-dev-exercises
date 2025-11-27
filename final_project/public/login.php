<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password && login($username, $password)) {
        // redirect by role
        $user = current_user();
        if ($user['role'] === 'admin') header('Location: /tp_web/final_project/admin/dashboard.php');
        elseif ($user['role'] === 'professor') header('Location: /tp_web/final_project/professor/sessions.php');
        else header('Location: /tp_web/final_project/student/courses.php');
        exit;
    } else {
        $message = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
  <h2>Login</h2>
  <?php if ($message) echo "<p style='color:red;'>".htmlspecialchars($message)."</p>"; ?>
  <form method="post">
    <label>Username: <input name="username"></label><br>
    <label>Password: <input type="password" name="password"></label><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>
