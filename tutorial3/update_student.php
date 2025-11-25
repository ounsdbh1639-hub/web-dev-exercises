<?php
require "db_connect.php";
$conn = getConnection();

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $matricule = $_POST['matricule'];
    $group_id = $_POST['group_id'];

    $stmt = $conn->prepare("UPDATE students SET fullname=?, matricule=?, group_id=? WHERE id=?");
    $stmt->execute([$fullname, $matricule, $group_id, $id]);

    echo "Student updated!";
    exit;
}

$student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch();
?>

<form method="POST">
    Full name: <input name="fullname" value="<?= $student['fullname'] ?>"><br>
    Matricule: <input name="matricule" value="<?= $student['matricule'] ?>"><br>
    Group ID: <input name="group_id" value="<?= $student['group_id'] ?>"><br>
    <button>Update</button>
</form>
