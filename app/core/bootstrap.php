<?php
declare(strict_types=1);

// Remember where the project folder is
define('APP_ROOT', dirname(__DIR__, 2));

// Where the page files (the HTML) are kept
define('VIEWS_DIR', (PHP_SAPI === 'cli-server' && getenv('FC_VIEWS_DIR'))
    ? getenv('FC_VIEWS_DIR')
    : APP_ROOT . '/app/views');

// Never show error details to visitors
ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e): void {
    error_log('Unhandled ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo 'Something went wrong. Please try again.';
});

// Connect to the database
require APP_ROOT . '/config/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/Auth.php';

// Load the other PHP files when they are needed
spl_autoload_register(function (string $class): void {
    foreach (['models', 'controllers'] as $dir) {
        $file = APP_ROOT . "/app/$dir/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// Safer settings for the login memory
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('FCSESSID');
// Start the login memory (it remembers who is logged in)
session_start();
