<?php
// includes/auth.php
require_once __DIR__ . '/db_connect.php';
session_start();

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'public/login.php');
        exit;
    }
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_role(string $role) {
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== $role) {
        http_response_code(403);
        echo "Forbidden: you don't have permission to access this page.";
        exit;
    }
}

function login_user(string $username, string $password): bool {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, fullname FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        // regenerate session id
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'fullname' => $user['fullname']
        ];
        return true;
    }
    return false;
}

function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', time() - 42000);
    }
    session_destroy();
}

