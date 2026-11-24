<?php
declare(strict_types=1);

// Talks to the shift table in the database
final class ShiftModel
{

    // Get the shifts of one person
    public static function forUser(PDO $pdo, int $userId, string $from, string $to): array
    {
        $st = $pdo->prepare(
            'SELECT sh.shift_id, sh.store_id, s.name AS store_name, sh.shift_date, sh.start_time, sh.end_time
             FROM shift sh JOIN store s ON s.store_id = sh.store_id
             WHERE sh.user_id = ? AND sh.shift_date BETWEEN ? AND ?
             ORDER BY sh.shift_date, sh.start_time'
        );
        $st->execute([$userId, $from, $to]);
        return $st->fetchAll();
    }

    // Get the shifts of everybody between two dates
    public static function forRange(PDO $pdo, string $from, string $to, ?int $storeId = null): array
    {
        $sql = 'SELECT sh.shift_id, sh.store_id, s.name AS store_name, sh.user_id,
                       u.first_name, u.last_name, sh.shift_date, sh.start_time, sh.end_time
                FROM shift sh
                JOIN store s ON s.store_id = sh.store_id
                JOIN user u ON u.user_id = sh.user_id
                WHERE sh.shift_date BETWEEN ? AND ?';
        $args = [$from, $to];
        if ($storeId !== null) {
            $sql .= ' AND sh.store_id = ?';
            $args[] = $storeId;
        }
        $st = $pdo->prepare($sql . ' ORDER BY sh.shift_date, sh.start_time, u.last_name');
        $st->execute($args);
        return $st->fetchAll();
    }

    // Get one shift
    public static function find(PDO $pdo, int $shiftId): ?array
    {
        $st = $pdo->prepare('SELECT * FROM shift WHERE shift_id = ?');
        $st->execute([$shiftId]);
        return $st->fetch() ?: null;
    }

    // Check that the shift form is filled in correctly
    public static function validate(array $in): array
    {
        $errors = [];
        $storeId = filter_var($in['store_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $userId  = filter_var($in['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $date    = is_string($in['shift_date'] ?? null) ? trim($in['shift_date']) : '';
        $start   = is_string($in['start_time'] ?? null) ? normalize_time(trim($in['start_time'])) : null;
        $end     = is_string($in['end_time'] ?? null) ? normalize_time(trim($in['end_time'])) : null;

        if ($storeId === false) { $errors[] = 'Choose a store.'; }
        if ($userId === false)  { $errors[] = 'Choose an employee.'; }
        if (!valid_date($date)) { $errors[] = 'Enter a valid date (YYYY-MM-DD).'; }
        if ($start === null)    { $errors[] = 'Enter a valid start time (HH:MM).'; }
        if ($end === null)      { $errors[] = 'Enter a valid end time (HH:MM).'; }
        if ($start !== null && $end !== null && $end <= $start) {
            $errors[] = 'End time must be after start time.';
        }
        if ($errors) {
            return [null, $errors];
        }
        return [[
            'store_id'   => $storeId,
            'user_id'    => $userId,
            'shift_date' => $date,
            'start_time' => $start,
            'end_time'   => $end,
        ], []];
    }

    // Add a new shift
    public static function create(PDO $pdo, array $input): array
    {
        return self::save($pdo, null, $input);
    }

    // Change a shift
    public static function update(PDO $pdo, int $shiftId, array $input): array
    {
        if (self::find($pdo, $shiftId) === null) {
            return [null, ['That shift no longer exists.']];
        }
        return self::save($pdo, $shiftId, $input);
    }

    // Remove a shift
    public static function delete(PDO $pdo, int $shiftId): bool
    {
        $st = $pdo->prepare('DELETE FROM shift WHERE shift_id = ?');
        $st->execute([$shiftId]);
        return $st->rowCount() > 0;
    }

    // Save a new or changed shift if it is allowed
    private static function save(PDO $pdo, ?int $shiftId, array $input): array
    {
        [$v, $errors] = self::validate($input);
        if ($errors) {
            return [null, $errors];
        }
        if (!StoreModel::exists($pdo, $v['store_id'])) {
            return [null, ['That store does not exist.']];
        }

        $pdo->beginTransaction();
        try {

            // Lock this person so nobody else can book them at the same moment
            $lock = $pdo->prepare('SELECT user_id FROM user WHERE user_id = ? FOR UPDATE');
            $lock->execute([$v['user_id']]);
            if (!$lock->fetchColumn()) {
                $pdo->rollBack();
                return [null, ['That employee does not exist.']];
            }

            if ($shiftId !== null) {

                $own = $pdo->prepare('SELECT shift_id FROM shift WHERE shift_id = ? FOR UPDATE');
                $own->execute([$shiftId]);
                if (!$own->fetchColumn()) {
                    $pdo->rollBack();
                    return [null, ['That shift no longer exists.']];
                }
            }

            // Look for another shift of this person at the same time
            $ov = $pdo->prepare(
                'SELECT sh.shift_id, s.name, sh.start_time, sh.end_time
                 FROM shift sh JOIN store s ON s.store_id = sh.store_id
                 WHERE sh.user_id = ? AND sh.shift_date = ?
                   AND sh.start_time < ? AND sh.end_time > ?
                   AND sh.shift_id <> ?
                 LIMIT 1'
            );
            $ov->execute([$v['user_id'], $v['shift_date'], $v['end_time'], $v['start_time'], $shiftId ?? 0]);
            if ($clash = $ov->fetch()) {
                $pdo->rollBack();
                return [null, [sprintf(
                    'Overlaps an existing shift at %s (%s-%s).',
                    $clash['name'], substr($clash['start_time'], 0, 5), substr($clash['end_time'], 0, 5)
                )]];
            }

            if ($shiftId === null) {
                $st = $pdo->prepare(
                    'INSERT INTO shift (store_id, user_id, shift_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)'
                );
                $st->execute([$v['store_id'], $v['user_id'], $v['shift_date'], $v['start_time'], $v['end_time']]);
                $shiftId = (int) $pdo->lastInsertId();
            } else {
                $st = $pdo->prepare(
                    'UPDATE shift SET store_id = ?, user_id = ?, shift_date = ?, start_time = ?, end_time = ?
                     WHERE shift_id = ?'
                );
                $st->execute([$v['store_id'], $v['user_id'], $v['shift_date'], $v['start_time'], $v['end_time'], $shiftId]);
            }
            $pdo->commit();
            return [$shiftId, []];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('ShiftModel::save failed: ' . $e->getMessage());
            return [null, ['Could not save the shift. Please try again.']];
        }
    }
}
