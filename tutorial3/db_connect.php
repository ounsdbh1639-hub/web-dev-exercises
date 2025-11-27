<?php
// FILE: tp web/tutorial 3/db_connect.php

function getConnection() {
    $config = include "config.php";

    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['database']}",
            $config['username'],
            $config['password']
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;

    } catch (PDOException $e) {
        file_put_contents("db_errors.log", $e->getMessage() . PHP_EOL, FILE_APPEND);
        return null;
    }
}
