<?php
/**
 * Shifts with no employee yet (table: shift_posts).
 * Managers post them; employees see the open ones for their own store.
 */
class ShiftPost extends Model
{
    /** Every post, newest week first, for the manager page. */
    public function all(): array
    {
        return $this->rows(
            'SELECT p.*, s.name AS store_name
             FROM shift_posts p
             JOIN stores s ON s.id = p.store_id
             ORDER BY p.work_date, p.start_time'
        );
    }

    /** The open posts of one store, for the staff page. */
    public function open_for_store(int $storeId): array
    {
        return $this->rows(
            "SELECT p.*, s.name AS store_name
             FROM shift_posts p
             JOIN stores s ON s.id = p.store_id
             WHERE p.store_id = :store AND p.status = 'open'
             ORDER BY p.work_date, p.start_time",
            ['store' => $storeId]
        );
    }

    /** Insert a post and return its new id. */
    public function create(array $post): int
    {
        $this->execute(
            'INSERT INTO shift_posts (store_id, work_date, start_time, end_time, needed, created_by)
             VALUES (:store, :date, :start, :end, :needed, :creator)',
            [
                'store'   => $post['store_id'],
                'date'    => $post['work_date'],
                'start'   => $post['start_time'],
                'end'     => $post['end_time'],
                'needed'  => $post['needed'],
                'creator' => $post['created_by'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }
}
