<?php
require "db_connect.php";

$conn = getConnection();

if ($conn) {
    echo "Connection successful!";
} else {
    echo "Connection failed.";
}
