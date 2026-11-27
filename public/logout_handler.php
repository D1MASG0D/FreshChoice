<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SecurityBootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('405 Method Not Allowed');
}

$csrfToken = $_POST['csrf_token'] ?? null;

if (!Security::verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    exit('403 Invalid CSRF token');
}

Security::destroySession();
header('Location: /login.htm');
exit;
