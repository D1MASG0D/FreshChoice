<?php

declare(strict_types=1);

class AccessControl
{
    public static function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $plainPassword, string $storedHash): bool
    {
        return password_verify($plainPassword, $storedHash);
    }

    public static function needsRehash(string $storedHash): bool
    {
        return password_needs_rehash($storedHash, PASSWORD_DEFAULT);
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
    }

    public static function currentUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function currentUserRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function currentStoreId(): ?int
    {
        return isset($_SESSION['store_id']) ? (int)$_SESSION['store_id'] : null;
    }

    public static function requireLogin(string $redirectUrl = '/login'): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    public static function requireRole(string $requiredRole): void
    {
        self::requireLogin();

        if (self::currentUserRole() !== $requiredRole) {
            http_response_code(403);
            exit('403 Forbidden: insufficient permissions.');
        }
    }

    public static function requireManager(): void
    {
        self::requireRole('manager');
    }

    public static function canAccessUserAccount(int $targetUserId, ?int $targetStoreId = null): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        if (self::currentUserId() === $targetUserId) {
            return true;
        }

        if (self::currentUserRole() === 'manager') {
            if ($targetStoreId === null) {
                return true;
            }
            return self::currentStoreId() === $targetStoreId;
        }

        return false;
    }

    public static function requireUserAccountAccess(int $targetUserId, ?int $targetStoreId = null): void
    {
        if (!self::canAccessUserAccount($targetUserId, $targetStoreId)) {
            http_response_code(403);
            exit('403 Forbidden: cannot access this account.');
        }
    }
}
