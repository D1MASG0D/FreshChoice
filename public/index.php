<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/core/bootstrap.php';

// List of pages: page name, how it is sent, which code handles it
$routes = [
    'login'            => ['GET',  'AuthController',     'showLogin'],
    'login-submit'     => ['POST', 'AuthController',     'login'],
    'logout'           => ['POST', 'AuthController',     'logout'],

    'my-schedule'      => ['GET',  'EmployeeController', 'schedule'],

    'manager-shifts'   => ['GET',  'ManagerController',  'shifts'],
    'shift-create'     => ['POST', 'ManagerController',  'shiftCreate'],
    'shift-update'     => ['POST', 'ManagerController',  'shiftUpdate'],
    'shift-delete'     => ['POST', 'ManagerController',  'shiftDelete'],
    'manager-accounts' => ['GET',  'ManagerController',  'accounts'],
    'account-create'   => ['POST', 'ManagerController',  'accountCreate'],
];

// Find out which page was asked for
$page = $_GET['page'] ?? '';
if (!is_string($page) || $page === '') {
    redirect(Auth::check() ? Auth::homePage() : 'login');
}

// Stop if the page does not exist
if (!isset($routes[$page])) {
    http_response_code(404);
    exit('Page not found.');
}

[$method, $class, $action] = $routes[$page];
// Stop if the page was asked in the wrong way
if ($_SERVER['REQUEST_METHOD'] !== $method) {
    http_response_code(405);
    header('Allow: ' . $method);
    exit('Method not allowed.');
}

// Run the code for this page
$class::$action($pdo);
