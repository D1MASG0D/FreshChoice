<?php
declare(strict_types=1);

// Make text safe to show on a page
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Make a link to one of our pages
function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

// Send the visitor to another page
function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

// Save a message to show on the next page
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

// Get the saved messages and clear them
function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// A secret code that proves a form came from our site
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Put the secret code inside a form
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

// Stop the request if the secret code is wrong
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Invalid or missing CSRF token.');
    }
}

// Show a page file and give it its data
function render(string $view, array $data = [], int $status = 200): void
{
    $file = VIEWS_DIR . '/' . $view . '.php';
    if (!is_file($file)) {
        error_log("View not found: $file");
        http_response_code(501);
        exit('This page has no template yet.');
    }
    http_response_code($status);
    extract($data, EXTR_SKIP);
    require $file;
}

// Send data in a simple format for JavaScript
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Check that a date is real, like 2026-12-31
function valid_date(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

// Get the from and to dates from the link
function date_range_from_request(): array
{
    $from = is_string($_GET['from'] ?? null) && valid_date($_GET['from'])
        ? $_GET['from']
        : (new DateTimeImmutable('monday this week'))->format('Y-m-d');
    $to = is_string($_GET['to'] ?? null) && valid_date($_GET['to'])
        ? $_GET['to']
        : (new DateTimeImmutable($from))->modify('+27 days')->format('Y-m-d');

    if ($to < $from) {
        $to = $from;
    }
    $max = (new DateTimeImmutable($from))->modify('+365 days')->format('Y-m-d');
    return [$from, min($to, $max)];
}

// Check a time like 09:30 and make it look the same every time
function normalize_time(string $t): ?string
{
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $t, $m)) {
        return null;
    }
    return sprintf('%s:%s:%s', $m[1], $m[2], $m[3] ?? '00');
}
