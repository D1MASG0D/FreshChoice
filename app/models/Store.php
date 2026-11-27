<?php
/**
 * The three supermarkets (table: stores).
 */
class Store extends Model
{
    /** All stores, alphabetical, for the filter and the form dropdowns. */
    public function all(): array
    {
        return $this->rows('SELECT * FROM stores ORDER BY name');
    }

    /** One store, or null when the id does not exist. */
    public function find(int $id): ?array
    {
        return $this->row('SELECT * FROM stores WHERE id = :id', ['id' => $id]);
    }
}
