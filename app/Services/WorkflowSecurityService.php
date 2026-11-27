<?php

declare(strict_types=1);

class WorkflowSecurityService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function validateDayOffRequest(string $startDate, string $endDate, string $reason, ?string $csrfToken): array
    {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return ['valid' => false, 'error' => 'Invalid security token.'];
        }

        $start = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);

        if (!$start || $start->format('Y-m-d') !== $startDate || !$end || $end->format('Y-m-d') !== $endDate) {
            return ['valid' => false, 'error' => 'Invalid date format.'];
        }

        if ($end < $start) {
            return ['valid' => false, 'error' => 'End date cannot be earlier than start date.'];
        }

        $cleanReason = trim($reason);
        if ($cleanReason === '' || mb_strlen($cleanReason) > 255) {
            return ['valid' => false, 'error' => 'Reason is required and must be under 255 characters.'];
        }

        return [
            'valid'      => true,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'reason'     => $cleanReason,
        ];
    }

    public function validateAvailabilitySlot(int $weekday, string $startTime, string $endTime, ?string $csrfToken): array
    {
        if (!Security::verifyCsrfToken($csrfToken)) {
            return ['valid' => false, 'error' => 'Invalid security token.'];
        }

        if ($weekday < 1 || $weekday > 7) {
            return ['valid' => false, 'error' => 'Invalid weekday selected.'];
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $startTime) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $endTime)) {
            return ['valid' => false, 'error' => 'Invalid time format.'];
        }

        if (strtotime($endTime) <= strtotime($startTime)) {
            return ['valid' => false, 'error' => 'End time must be after start time.'];
        }

        return [
            'valid'      => true,
            'weekday'    => $weekday,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];
    }

    public function canManagerReviewRequest(int $requestId): bool
    {
        if (AccessControl::currentUserRole() !== 'manager') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT u.store_id
             FROM requests r
             INNER JOIN users u ON r.user_id = u.id
             WHERE r.id = :request_id
             LIMIT 1'
        );
        $stmt->execute([':request_id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return (int)$row['store_id'] === AccessControl::currentStoreId();
    }

    public function canManagerReviewAvailability(int $availabilityId): bool
    {
        if (AccessControl::currentUserRole() !== 'manager') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT u.store_id
             FROM availabilities a
             INNER JOIN users u ON a.user_id = u.id
             WHERE a.id = :availability_id
             LIMIT 1'
        );
        $stmt->execute([':availability_id' => $availabilityId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        return (int)$row['store_id'] === AccessControl::currentStoreId();
    }

    public function canPostShiftSwap(int $shiftId): bool
    {
        if (!AccessControl::isLoggedIn()) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT user_id, store_id FROM shifts WHERE id = :shift_id LIMIT 1');
        $stmt->execute([':shift_id' => $shiftId]);
        $shift = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shift) {
            return false;
        }

        if ((int)$shift['user_id'] === AccessControl::currentUserId()) {
            return true;
        }

        return AccessControl::currentUserRole() === 'manager'
            && (int)$shift['store_id'] === AccessControl::currentStoreId();
    }

    public function canClaimShiftPost(int $postId): bool
    {
        if (!AccessControl::isLoggedIn()) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT s.store_id, sp.posted_by_user_id, sp.status
             FROM shift_posts sp
             INNER JOIN shifts s ON sp.shift_id = s.id
             WHERE sp.id = :post_id
             LIMIT 1'
        );
        $stmt->execute([':post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post || $post['status'] !== 'open') {
            return false;
        }

        if ((int)$post['posted_by_user_id'] === AccessControl::currentUserId()) {
            return false;
        }

        return (int)$post['store_id'] === AccessControl::currentStoreId();
    }
}
