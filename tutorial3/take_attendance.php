<?php
// FILE: tp web/tutorial 3/take_attendance.php

require "db_connect.php";

$pdo = getConnection();
if (!$pdo) {
    echo "Database connection failed!";
    exit;
}

// 1. Load students from database
try {
    $stmt = $pdo->query("SELECT id, matricule, fullname FROM students ORDER BY fullname");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error loading students: " . $e->getMessage();
    exit;
}

// 2. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance = [];
    $today = date("Y-m-d");

    // Check if attendance for today already exists
    try {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE date = ?");
        $stmtCheck->execute([$today]);
        $count = $stmtCheck->fetchColumn();
        if ($count > 0) {
            echo "Attendance for today has already been taken.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Error checking attendance: " . $e->getMessage();
        exit;
    }

    // Insert a new session
    try {
        $stmtSession = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) VALUES (?, ?, ?, ?, 'closed')");
        // For now, course_id = 1, opened_by = 1 (placeholder), group_id = 'all'
        $stmtSession->execute([1, 'all', $today, 1]);
        $session_id = $pdo->lastInsertId();
    } catch (PDOException $e) {
        echo "Error creating attendance session: " . $e->getMessage();
        exit;
    }

    // Prepare attendance insert
    try {
        $stmtInsert = $pdo->prepare("INSERT INTO attendance (student_id, session_id, status) VALUES (?, ?, ?)");
        foreach ($students as $student) {
            $status = isset($_POST['status'][$student['id']]) ? $_POST['status'][$student['id']] : 'absent';
            $stmtInsert->execute([$student['id'], $session_id, $status]);
        }
        echo "Attendance recorded successfully!";
    } catch (PDOException $e) {
        echo "Error saving attendance: " . $e->getMessage();
    }
}
?>

<form method="POST">
    <table border="1" cellpadding="5">
        <tr>
            <th>Student</th>
            <th>Present</th>
            <th>Absent</th>
        </tr>
        <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['fullname'] . " (" . $student['matricule'] . ")") ?></td>
                <td><input type="radio" name="status[<?= $student['id'] ?>]" value="present" required></td>
                <td><input type="radio" name="status[<?= $student['id'] ?>]" value="absent"></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit">Submit Attendance</button>
</form>
