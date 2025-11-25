<?php
require "db_connect.php";
$conn = getConnection();

$id = $_GET['id'];

$conn->exec("UPDATE attendance_sessions SET status='closed' WHERE id=$id");

echo "Session closed!";
?>
