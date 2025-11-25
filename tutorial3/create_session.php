<?php
require "db_connect.php";
$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id = $_POST['course_id'];
    $group_id = $_POST['group_id'];
    $prof = $_POST['professor_id'];

    $stmt = $conn->prepare("
        INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status)
        VALUES (?, ?, CURDATE(), ?, 'open')
    ");

    $stmt->execute([$course_id, $group_id, $prof]);

    echo "Session created! ID = " . $conn->lastInsertId();
}
?>

<form method="POST">
    Course ID: <input name="course_id"><br>
    Group ID: <input name="group_id"><br>
    Professor ID: <input name="professor_id"><br>
    <button>Create Session</button>
</form>
