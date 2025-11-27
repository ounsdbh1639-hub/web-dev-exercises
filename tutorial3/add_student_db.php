<?php
require "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = $_POST['fullname'];
    $matricule = $_POST['matricule'];
    $group_id = $_POST['group_id'];

    $conn = getConnection();

    $sql = "INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$fullname, $matricule, $group_id]);

    echo "Student added to database!";
}
?>

<form method="POST">
    Full name: <input name="fullname"><br>
    Matricule: <input name="matricule"><br>
    Group ID: <input name="group_id"><br>
    <button>Add Student</button>
</form>
