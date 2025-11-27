<?php
// FILE: tp web/tutorial 3/add_student.php

require "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validate form
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $group = trim($_POST['group']);

    if ($student_id === "" || $name === "" || $group === "") {
        echo "All fields are required!";
        exit;
    }

    // 2. Connect to database
    $pdo = getConnection();
    if (!$pdo) {
        echo "Database connection failed!";
        exit;
    }

    // 3. Insert student into database
    try {
        $stmt = $pdo->prepare("INSERT INTO students (matricule, fullname, group_id) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $name, $group]);
        echo "Student added successfully!";
    } catch (PDOException $e) {
        echo "Error adding student: " . $e->getMessage();
    }
}
?>

<form method="POST">
    Student ID: <input type="text" name="student_id"><br>
    Name: <input type="text" name="name"><br>
    Group: <input type="text" name="group"><br>
    <button type="submit">Add Student</button>
</form>

