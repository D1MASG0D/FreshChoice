<?php
declare(strict_types=1);

// Everything about who is logged in
final class Auth
{

    // Get the logged in person
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    // Get the number of the logged in person
    public static function id(): ?int
    {
        return isset($_SESSION['user']['user_id']) ? (int) $_SESSION['user']['user_id'] : null;
    }

    // Is somebody logged in?
    public static function check(): bool
    {
        return self::user() !== null;
    }

    // Does the person have this role?
    public static function hasRole(string $role): bool
    {
        return (self::user()['role'] ?? null) === $role;
    }

    // Is the person a manager?
    public static function isManager(): bool
    {
        return self::hasRole('manager');
    }

    // Which page to show after login
    public static function homePage(): string
    {
        return self::isManager() ? 'manager-shifts' : 'my-schedule';
    }

    // Check the email and the secret word
    public static function attempt(PDO $pdo, string $email, string $password): ?array
    {
        $user = UserModel::findByEmail($pdo, $email);

        static $dummy = null;
        $dummy ??= password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $hash = $user['password_hash'] ?? $dummy;

        if (!password_verify($password, $hash) || $user === null) {
            return null;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            UserModel::updatePasswordHash($pdo, (int) $user['user_id'], password_hash($password, PASSWORD_DEFAULT));
        }
        return $user;
    }

    // Log the person in
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    // Log the person out
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?: 'Lax',
            ]);
        }
        session_destroy();
    }

    // Send to the login page if not logged in
    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('login');
        }
    }

    // Stop with an error if the person has the wrong role
    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (!self::hasRole($role)) {
            http_response_code(403);
            exit('Forbidden: you do not have access to this page.');
        }
    }
}
