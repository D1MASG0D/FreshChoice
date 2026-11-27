<?php
/**
 * Accounts (table: users).
 */
class User extends Model
{
    /** One account by id. */
    public function find(int $id): ?array
    {
        return $this->row('SELECT * FROM users WHERE id = :id', ['id' => $id]);
    }

    /** One account by e-mail, used by the login page. */
    public function find_by_email(string $email): ?array
    {
        return $this->row('SELECT * FROM users WHERE email = :email', ['email' => $email]);
    }

    /**
     * Every employee with their home store name, for the assign form.
     * Managers are left out: they are not scheduled.
     */
    public function employees(): array
    {
        return $this->rows(
            'SELECT u.id, u.first_name, u.last_name, s.name AS store_name
             FROM users u
             LEFT JOIN stores s ON s.id = u.store_id
             WHERE u.role = :role
             ORDER BY u.first_name, u.last_name',
            ['role' => 'employee']
        );
    }
}
