<?php

declare(strict_types=1);

class AuthService
{
    private PDO $pdo;
    private int $maxAttempts = 5;
    private int $lockoutSeconds = 300;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $password, ?string $csrfToken): array
    {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return ['success' => false, 'error' => 'Invalid security token. Please refresh and try again.'];
        }

        if ($this->isRateLimited()) {
            return ['success' => false, 'error' => 'Too many failed login attempts. Please try again in 5 minutes.'];
        }

        $cleanEmail = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        if ($cleanEmail === false || $password === '') {
            $this->recordFailedAttempt();
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        $stmt = $this->pdo->prepare(
            'SELECT id, store_id, role, first_name, last_name, email, password_hash
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $cleanEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !AccessControl::verifyPassword($password, $user['password_hash'])) {
            $this->recordFailedAttempt();
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if (AccessControl::needsRehash($user['password_hash'])) {
            $newHash = AccessControl::hashPassword($password);
            $updateStmt = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $updateStmt->execute([
                ':hash' => $newHash,
                ':id'   => $user['id'],
            ]);
        }

        $this->clearFailedAttempts();
        Security::regenerateSession();

        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['store_id']   = (int)$user['store_id'];
        $_SESSION['role']       = $user['role'];
        $_SESSION['name']       = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];

        return [
            'success' => true,
            'role'    => $user['role'],
        ];
    }

    public function logout(?string $csrfToken): bool
    {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return false;
        }

        Security::destroySession();
        return true;
    }

    private function isRateLimited(): bool
    {
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $firstAttemptTime = $_SESSION['login_first_attempt'] ?? 0;

        if ($attempts >= $this->maxAttempts) {
            if ((time() - $firstAttemptTime) < $this->lockoutSeconds) {
                return true;
            }
            $this->clearFailedAttempts();
        }

        return false;
    }

    private function recordFailedAttempt(): void
    {
        if (empty($_SESSION['login_first_attempt'])) {
            $_SESSION['login_first_attempt'] = time();
        }
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    }

    private function clearFailedAttempts(): void
    {
        unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt']);
    }
}
