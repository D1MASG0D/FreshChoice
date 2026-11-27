<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Core/SecurityBootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.htm');
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$csrfToken = $_POST['csrf_token'] ?? null;

$authService = new AuthService($pdo);
$result = $authService->login($email, $password, $csrfToken);

if ($result['success']) {
    if ($result['role'] === 'manager') {
        header('Location: /Manager%20Overview/overview.htm');
    } else {
        header('Location: /employee/dashboard.php');
    }
    exit;
}

$_SESSION['login_error'] = $result['error'];
header('Location: /login.htm');
exit;
