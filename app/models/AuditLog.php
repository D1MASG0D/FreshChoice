<?php
/**
 * The trail of every change (table: audit_log).
 * The row is always written inside the same transaction as the change,
 * so a failed change never leaves a trail behind.
 */
class AuditLog extends Model
{
    /**
     * $action is short and readable, for example "shift created".
     * $reason is the text the manager typed when overriding a warning.
     */
    public function write(int $userId, string $action, string $table, int $recordId, string $reason = ''): void
    {
        $this->execute(
            'INSERT INTO audit_log (user_id, action, table_name, record_id, reason)
             VALUES (:user, :action, :table_name, :record_id, :reason)',
            [
                'user'       => $userId,
                'action'     => $action,
                'table_name' => $table,
                'record_id'  => $recordId,
                'reason'     => $reason === '' ? null : $reason,
            ]
        );
    }
}
