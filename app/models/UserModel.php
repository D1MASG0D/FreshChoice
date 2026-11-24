<?php
declare(strict_types=1);

// Talks to the user table in the database
final class UserModel
{
    // Find a person by email
    public static function findByEmail(PDO $pdo, string $email): ?array
    {
        $st = $pdo->prepare('SELECT * FROM user WHERE email = ?');
        $st->execute([$email]);
        return $st->fetch() ?: null;
    }

    // Find a person by number
    public static function findById(PDO $pdo, int $id): ?array
    {
        $st = $pdo->prepare(
            'SELECT user_id, store_id, role, first_name, last_name, email FROM user WHERE user_id = ?'
        );
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // Is this email already used?
    public static function emailExists(PDO $pdo, string $email): bool
    {
        $st = $pdo->prepare('SELECT 1 FROM user WHERE email = ?');
        $st->execute([$email]);
        return (bool) $st->fetchColumn();
    }

    // Add a new person
    public static function create(
        PDO $pdo,
        int $storeId,
        string $role,
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash
    ): int {
        $st = $pdo->prepare(
            'INSERT INTO user (store_id, role, first_name, last_name, email, password_hash)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$storeId, $role, $firstName, $lastName, $email, $passwordHash]);
        return (int) $pdo->lastInsertId();
    }

    // Save a new scrambled secret word
    public static function updatePasswordHash(PDO $pdo, int $userId, string $hash): void
    {
        $st = $pdo->prepare('UPDATE user SET password_hash = ? WHERE user_id = ?');
        $st->execute([$hash, $userId]);
    }

    // Get all people with their store
    public static function listAll(PDO $pdo): array
    {
        return $pdo->query(
            'SELECT u.user_id, u.first_name, u.last_name, u.email, u.role, u.store_id, s.name AS store_name
             FROM user u JOIN store s ON s.store_id = u.store_id
             ORDER BY s.store_id, u.role, u.last_name, u.first_name'
        )->fetchAll();
    }

    // Get all people who can get a shift
    public static function listSchedulable(PDO $pdo): array
    {
        return $pdo->query(
            'SELECT u.user_id, u.first_name, u.last_name, u.store_id, s.name AS store_name
             FROM user u JOIN store s ON s.store_id = u.store_id
             ORDER BY u.last_name, u.first_name'
        )->fetchAll();
    }
}
