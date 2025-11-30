<?php
// public/index.php
require_once __DIR__ . '/../includes/auth.php';
if (!is_logged_in()) {
    header('Location: ' . BASE_URL . 'public/login.php');
    exit;
}
$user = current_user();
switch ($user['role']) {
    case 'admin':
        header('Location: ' . BASE_URL . 'admin/dashboard.php');
        break;
    case 'professor':
        header('Location: ' . BASE_URL . 'professor/sessions.php');
        break;
    case 'student':
        header('Location: ' . BASE_URL . 'student/courses.php');
        break;
    default:
        echo "Unknown role.";
}
