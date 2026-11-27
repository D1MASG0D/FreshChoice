<?php
/**
 * Global constants and the small helper functions every page uses.
 * Nothing here talks to the database.
 */

const APP_NAME        = 'FreshChoice';
const SHIFT_MIN_REST  = 11; // hours between the end of one shift and the start of the next
const MAX_WEEKS_AHEAD = 8;  // how far into the future a shift may be planned

define('BASE_PATH', dirname(__DIR__)); // the FreshChoice folder on disk

/**
 * The part of the URL the app is served from:
 * an empty string with PHP's built-in server,
 * or "/FreshChoice/public" when it sits inside XAMPP's htdocs.
 */
$scriptFolder = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
define('BASE_URL', $scriptFolder === '/' ? '' : rtrim($scriptFolder, '/'));

/** Escape text before printing it, so nothing typed by a user becomes HTML. */
function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** A full link inside the app. */
function url(string $path): string
{
    return BASE_URL . $path;
}

/** Send the browser somewhere else and stop this request. */
function redirect(string $path): never
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Store a one-time message for the next page.
 * $type is a Bootstrap alert name: success, danger, warning or info.
 */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

/** Read and clear the stored messages. */
function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $flashes;
}

/** A token that proves a form came from this site and not from another one. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }

    return $_SESSION['csrf'];
}

/** The hidden input that every form needs. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Stop the request when a posted form does not carry the right token. */
function check_csrf(): void
{
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? null)) {
        http_response_code(419);
        exit('Your session expired. Go back, reload the page and try again.');
    }
}

/** The signed-in user's id, or null when nobody is signed in. */
function user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/** "manager" or "employee", or null when nobody is signed in. */
function current_role(): ?string
{
    return $_SESSION['role'] ?? null;
}

function is_manager(): bool
{
    return current_role() === 'manager';
}

/**
 * Render a view inside the shared layout.
 * The view file receives every key of $data as its own variable.
 */
function view(string $file, array $data = [], string $title = ''): void
{
    $data['title'] = $title;
    extract($data);

    ob_start();
    require BASE_PATH . '/app/views/' . $file . '.php';
    $content = ob_get_clean();

    require BASE_PATH . '/app/views/layout.php';
}

/** Monday of the week that $date belongs to, as Y-m-d. */
function monday_of(string $date): string
{
    return (new DateTimeImmutable($date))->modify('monday this week')->format('Y-m-d');
}

/** The seven dates of a week, Monday first. */
function week_days(string $monday): array
{
    $days = [];
    $date = new DateTimeImmutable($monday);

    for ($i = 0; $i < 7; $i++) {
        $days[] = $date->modify('+' . $i . ' day')->format('Y-m-d');
    }

    return $days;
}

/** "Mon 24 Nov" for a date column heading. */
function day_label(string $date): string
{
    return (new DateTimeImmutable($date))->format('D j M');
}

/**
 * The planning window rule, in one place: a date must be a real date, not
 * in the past, and no further ahead than MAX_WEEKS_AHEAD.
 * Returns the problem as a sentence, or null when the date is fine.
 */
function date_error(string $value): ?string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

    if ($date === false || $date->format('Y-m-d') !== $value) {
        return 'Choose a valid date.';
    }

    $today = new DateTimeImmutable('today');

    if ($date < $today) {
        return 'The date is in the past.';
    }

    if ($date > $today->modify('+' . MAX_WEEKS_AHEAD . ' weeks')) {
        return 'The date is more than ' . MAX_WEEKS_AHEAD . ' weeks ahead.';
    }

    return null;
}

/**
 * The time rule: both times filled in, and the end after the start.
 * Returns the problem as a sentence, or null when the pair is fine.
 */
function time_error(string $start, string $end): ?string
{
    $pattern = '/^\d{2}:\d{2}$/';

    if (! preg_match($pattern, $start) || ! preg_match($pattern, $end)) {
        return 'Fill in a start and an end time.';
    }

    if ($end <= $start) {
        return 'The end time must be after the start time.';
    }

    return null;
}

/** MariaDB returns times as "09:00:00"; the pages show "09:00". */
function hhmm(?string $time): string
{
    return $time === null ? '' : substr($time, 0, 5);
}
