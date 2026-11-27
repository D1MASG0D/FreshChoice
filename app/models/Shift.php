<?php
/**
 * Scheduled shifts (table: shifts). One row = one employee, one store,
 * one date and one time range.
 */
class Shift extends Model
{
    /**
     * Every shift in a date range, already joined with the employee and
     * the store, ordered the way the calendar reads.
     * $storeId = 0 means all three stores.
     */
    public function for_week(string $monday, int $storeId = 0): array
    {
        $sunday = date('Y-m-d', strtotime($monday . ' +6 days'));

        $sql = 'SELECT s.id, s.work_date, s.start_time, s.end_time, s.store_id,
                       u.first_name, u.last_name,
                       st.name AS store_name
                FROM shifts s
                JOIN users u   ON u.id = s.user_id
                JOIN stores st ON st.id = s.store_id
                WHERE s.work_date BETWEEN :monday AND :sunday';

        $params = ['monday' => $monday, 'sunday' => $sunday];

        if ($storeId > 0) {
            $sql .= ' AND s.store_id = :store';
            $params['store'] = $storeId;
        }

        $sql .= ' ORDER BY s.work_date, s.start_time';

        return $this->rows($sql, $params);
    }

    /** One shift by id, or null when it was deleted. */
    public function find(int $id): ?array
    {
        return $this->row('SELECT * FROM shifts WHERE id = :id', ['id' => $id]);
    }

    /**
     * Every shift of one employee between two dates.
     * The staff page uses it, and the conflict checks use a three-day window.
     * $exceptId leaves out the shift being edited, so it cannot clash with itself.
     */
    public function for_employee(int $userId, string $from, string $to, int $exceptId = 0): array
    {
        return $this->rows(
            'SELECT * FROM shifts
             WHERE user_id = :user
               AND work_date BETWEEN :from AND :to
               AND id <> :except
             ORDER BY work_date, start_time',
            ['user' => $userId, 'from' => $from, 'to' => $to, 'except' => $exceptId]
        );
    }

    /** How many people are already scheduled in one store on one day. */
    public function count_in_store(int $storeId, string $date, int $exceptId = 0): int
    {
        return (int) $this->scalar(
            'SELECT COUNT(*) FROM shifts
             WHERE store_id = :store AND work_date = :date AND id <> :except',
            ['store' => $storeId, 'date' => $date, 'except' => $exceptId]
        );
    }

    /** Insert a shift and return its new id. */
    public function create(array $shift): int
    {
        $this->execute(
            'INSERT INTO shifts (store_id, user_id, work_date, start_time, end_time)
             VALUES (:store, :user, :date, :start, :end)',
            [
                'store' => $shift['store_id'],
                'user'  => $shift['user_id'],
                'date'  => $shift['work_date'],
                'start' => $shift['start_time'],
                'end'   => $shift['end_time'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /** Change an existing shift. */
    public function update(int $id, array $shift): void
    {
        $this->execute(
            'UPDATE shifts
             SET store_id = :store, user_id = :user, work_date = :date,
                 start_time = :start, end_time = :end
             WHERE id = :id',
            [
                'store' => $shift['store_id'],
                'user'  => $shift['user_id'],
                'date'  => $shift['work_date'],
                'start' => $shift['start_time'],
                'end'   => $shift['end_time'],
                'id'    => $id,
            ]
        );
    }

    /** Remove a shift. */
    public function delete(int $id): void
    {
        $this->execute('DELETE FROM shifts WHERE id = :id', ['id' => $id]);
    }
}
