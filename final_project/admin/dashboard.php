<?php
session_start();

// User must be logged in
if (!isset($_SESSION['user'])) {
    header('Location: /tp_web/final_project/public/login.php');
    exit;
}

// Logged-in admin info
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }
        h1 {
            color: #333;
        }
        .box {
            background: white;
            padding: 20px;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        a {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            font-size: 18px;
            color: #0066cc;
        }
        a:hover {
            text-decoration: underline;
        }
        .logout {
            color: red;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>

    <p>Your role: <strong><?php echo $user['role']; ?></strong></p>

    <a href="/tp_web/final_project/admin/students.php">📘 Manage Students</a>
    <a href="/tp_web/final_project/professor/sessions.php">📝 Manage Sessions</a>
    <a href="/tp_web/final_project/student/courses.php">📚 Student View</a>

    <a class="logout" href="/tp_web/final_project/public/logout.php">🚪 Logout</a>
</div>

</body>
</html>
