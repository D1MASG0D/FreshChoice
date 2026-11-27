<?php
/**
 * Base class for every model.
 *
 * A model is the only kind of file that writes SQL. It receives the shared
 * PDO connection and uses the four helpers below, so every query in the
 * project is a prepared statement by default.
 */
class Model
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /** Run a SELECT and return all rows. */
    protected function rows(string $sql, array $params = []): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /** Run a SELECT and return the first row, or null when nothing matched. */
    protected function row(string $sql, array $params = []): ?array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** Run a SELECT for a single value, such as COUNT(*) or MAX(end_time). */
    protected function scalar(string $sql, array $params = [])
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn();
    }

    /** Run an INSERT, UPDATE or DELETE. Returns how many rows changed. */
    protected function execute(string $sql, array $params = []): int
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }
}
