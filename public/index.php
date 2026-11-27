<?php
/**
 * The single entry point.
 *
 * Every request - page or form post - arrives here, is matched against the
 * route table below, checked for the right role, and then handed to a
 * controller. Nothing else in the project is reachable from the browser.
 */

// With PHP's built-in development server, let real files (css, images) be
// served directly instead of passing them through the router.
if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($requested)) {
        return false;
    }
}

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/db.php';

// The session cookie cannot be read by JavaScript, and it is not sent
// along with requests made by other sites.
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require BASE_PATH . '/app/core/Model.php';
foreach (glob(BASE_PATH . '/app/models/*.php') as $file) {
    require $file;
}
foreach (glob(BASE_PATH . '/app/controllers/*.php') as $file) {
    require $file;
}

// Work out which page was asked for.
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (BASE_URL !== '' && str_starts_with($path, BASE_URL)) {
    $path = substr($path, strlen(BASE_URL));
}

$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$key = $method . ' ' . $path;

/**
 * The route table: address => [controller, method, who may open it].
 *
 * "public"   anyone, signed in or not
 * "any"      anyone signed in
 * "manager"  managers only
 * "employee" employees only
 */
$routes = [
    'GET /'              => ['AuthController', 'home', 'public'],
    'GET /login'         => ['AuthController', 'showLogin', 'public'],
    'POST /login'        => ['AuthController', 'login', 'public'],
    'POST /logout'       => ['AuthController', 'logout', 'any'],

    'GET /calendar'      => ['ScheduleController', 'calendar', 'manager'],
    'GET /shift/new'     => ['ScheduleController', 'create', 'manager'],
    'POST /shift/save'   => ['ScheduleController', 'store', 'manager'],
    'GET /shift/edit'    => ['ScheduleController', 'edit', 'manager'],
    'POST /shift/delete' => ['ScheduleController', 'destroy', 'manager'],

    'GET /posts'         => ['ShiftPostController', 'create', 'manager'],
    'POST /posts/save'   => ['ShiftPostController', 'store', 'manager'],

    'GET /my-schedule'   => ['ScheduleController', 'mySchedule', 'employee'],
    'GET /open-shifts'   => ['ShiftPostController', 'index', 'employee'],
];

if (! isset($routes[$key])) {
    http_response_code(404);
    view('errors/404', [], 'Page not found');
    exit;
}

[$controllerName, $action, $role] = $routes[$key];

// The role guard lives here, once, so a new page cannot forget it.
// 1. Every page except the public ones needs a signed-in user.
if ($role !== 'public' && user_id() === null) {
    redirect('/login');
}

// 2. Manager pages are closed to employees.
if ($role === 'manager' && ! is_manager()) {
    http_response_code(403);
    view('errors/403', [], 'Not allowed');
    exit;
}

// 3. Managers are not scheduled, so they have no staff schedule.
if ($role === 'employee' && is_manager()) {
    redirect('/calendar');
}

$controller = new $controllerName(db());
$controller->$action();
