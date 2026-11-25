<?php

declare(strict_types=1);

class AccountSecurityService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSafeUserProfile(int $targetUserId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id, store_id, role, first_name, last_name, email
             FROM user
             WHERE user_id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $targetUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        AccessControl::requireUserAccountAccess(
            (int)$user['user_id'],
            (int)$user['store_id']
        );

        return $user;
    }

    public function validatePasswordStrength(string $password): array
    {
        if (strlen($password) < 8) {
            return ['valid' => false, 'error' => 'Password must be at least 8 characters long.'];
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must include uppercase, lowercase, and a number.'];
        }
        return ['valid' => true, 'error' => null];
    }

    public function changePassword(
        int $targetUserId,
        string $currentPassword,
        string $newPassword,
        ?string $csrfToken
    ): array {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return ['success' => false, 'error' => 'Invalid security token.'];
        }

        if (AccessControl::currentUserId() !== $targetUserId) {
            return ['success' => false, 'error' => 'Unauthorized password change attempt.'];
        }

        $strength = $this->validatePasswordStrength($newPassword);
        if (!$strength['valid']) {
            return ['success' => false, 'error' => $strength['error']];
        }

        $stmt = $this->pdo->prepare('SELECT password_hash FROM user WHERE user_id = :id LIMIT 1');
        $stmt->execute([':id' => $targetUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !AccessControl::verifyPassword($currentPassword, $row['password_hash'])) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        $newHash = AccessControl::hashPassword($newPassword);
        $update = $this->pdo->prepare('UPDATE user SET password_hash = :hash WHERE user_id = :id');
        $update->execute([
            ':hash' => $newHash,
            ':id'   => $targetUserId,
        ]);

        Security::regenerateSession();

        return ['success' => true];
    }

    public function managerResetEmployeePassword(
        int $targetUserId,
        string $temporaryPassword,
        ?string $csrfToken
    ): array {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return ['success' => false, 'error' => 'Invalid security token.'];
        }

        AccessControl::requireManager();

        $profile = $this->getSafeUserProfile($targetUserId);
        if (!$profile) {
            return ['success' => false, 'error' => 'Employee not found.'];
        }

        $strength = $this->validatePasswordStrength($temporaryPassword);
        if (!$strength['valid']) {
            return ['success' => false, 'error' => $strength['error']];
        }

        $newHash = AccessControl::hashPassword($temporaryPassword);
        $update = $this->pdo->prepare('UPDATE user SET password_hash = :hash WHERE user_id = :id');
        $update->execute([
            ':hash' => $newHash,
            ':id'   => $targetUserId,
        ]);

        return ['success' => true];
    }
}
