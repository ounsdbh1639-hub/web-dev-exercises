<?php
// includes/db_connect.php
$config = include __DIR__ . '/config.php';

function getPDO() {
    global $config;
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        file_put_contents(__DIR__ . '/db_errors.log', date('c') . " - " . $e->getMessage() . PHP_EOL, FILE_APPEND);
        return null;
    }
}
