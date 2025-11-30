<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Attendance System</title>
<link href="<?= BASE_URL ?>public/css/style.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>public/index.php">AttendanceSys</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">
      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
            <?php if ($user['role'] === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">Admin</a></li>
            <?php endif; ?>
            <?php if ($user['role'] === 'professor'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>professor/sessions.php">Professor</a></li>
            <?php endif; ?>
            <?php if ($user['role'] === 'student'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>student/courses.php">Student</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>public/logout.php">Logout (<?= htmlspecialchars($user['fullname']) ?>)</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>public/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

