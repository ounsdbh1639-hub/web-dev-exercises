<?php
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: /tp_web/final_project/public/login.php');
