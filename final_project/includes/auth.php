<?php
// includes/auth.php
session_start();
require_once __DIR__ . '/db_connect.php';

function login($username, $password) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, username, password_hash, role, fullname FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        // store minimal info
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'fullname' => $user['fullname']
        ];
        return true;
    }
    return false;
}

function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: /final_project/public/login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo "Forbidden - insufficient role.";
        exit;
    }
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function logout() {
    session_destroy();
}
