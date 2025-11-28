<?php
// final_project/includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance System</title>
  <link rel="stylesheet" href="/tp_web/final_project/public/css/style.css">
</head>
<body>
<header style="background:#fff;padding:10px 0;border-bottom:1px solid #eee">
  <div class="container" style="display:flex;justify-content:space-between;align-items:center">
    <div><a href="/tp_web/final_project/public/index.php" style="text-decoration:none;font-weight:700;color:var(--primary)">Attendance System</a></div>
    <nav>
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="/tp_web/final_project/admin/students.php">Students</a> |
          <a href="/tp_web/final_project/admin/import.php">Import</a> |
          <a href="/tp_web/final_project/admin/dashboard.php">Dashboard</a>
        <?php elseif ($user['role'] === 'professor'): ?>
          <a href="/tp_web/final_project/professor/sessions.php">My Sessions</a>
        <?php else: ?>
          <a href="/tp_web/final_project/student/courses.php">My Courses</a>
        <?php endif; ?>
        | <a href="/tp_web/final_project/public/logout.php">Logout</a>
      <?php else: ?>
        <a href="/tp_web/final_project/public/login.php">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
