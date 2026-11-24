<?php
declare(strict_types=1);

// Talks to the store table in the database
final class StoreModel
{
    // Get all stores
    public static function all(PDO $pdo): array
    {
        return $pdo->query('SELECT store_id, name, address FROM store ORDER BY store_id')->fetchAll();
    }

    // Does this store exist?
    public static function exists(PDO $pdo, int $storeId): bool
    {
        $st = $pdo->prepare('SELECT 1 FROM store WHERE store_id = ?');
        $st->execute([$storeId]);
        return (bool) $st->fetchColumn();
    }
}
