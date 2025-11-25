<?php
require "db_connect.php";
$conn = getConnection();

$id = $_GET['id'];

$conn->exec("DELETE FROM students WHERE id=$id");

echo "Deleted!";
?>
