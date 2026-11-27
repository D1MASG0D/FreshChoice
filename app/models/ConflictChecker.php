<?php
/**
 * The five checks that run before a shift is saved.
 *
 * Three answers block the save (data that cannot be right) and three warn
 * (a judgement call the manager may override with a written reason):
 *
 *   1. overlap with another shift on the same day      -> blocks
 *   2. a second store on the same day                  -> blocks
 *   3. less than 11 hours of rest around the shift     -> warns
 *   4. the employee marked the day unavailable         -> warns
 *   5. the store is left with too few people that day  -> warns
 */
class ConflictChecker extends Model
{
    private Shift $shifts;
    private Store $stores;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
        $this->shifts = new Shift($db);
        $this->stores = new Store($db);
    }

    /**
     * $shift holds user_id, store_id, work_date, start_time and end_time.
     * $exceptId is the id of the shift being edited (0 for a new shift).
     *
     * Returns ['errors' => [...], 'warnings' => [...]] as readable sentences.
     */
    public function check(array $shift, int $exceptId = 0): array
    {
        $errors   = [];
        $warnings = [];

        $userId  = (int) $shift['user_id'];
        $storeId = (int) $shift['store_id'];
        $date    = $shift['work_date'];

        // One day back and one day forward is enough for every check below.
        $from = date('Y-m-d', strtotime($date . ' -1 day'));
        $to   = date('Y-m-d', strtotime($date . ' +1 day'));

        $nearby = $this->shifts->for_employee($userId, $from, $to, $exceptId);

        $newStart = new DateTimeImmutable($date . ' ' . $shift['start_time']);
        $newEnd   = new DateTimeImmutable($date . ' ' . $shift['end_time']);

        foreach ($nearby as $other) {
            $otherStart = new DateTimeImmutable($other['work_date'] . ' ' . $other['start_time']);
            $otherEnd   = new DateTimeImmutable($other['work_date'] . ' ' . $other['end_time']);
            $sameDay    = $other['work_date'] === $date;

            // 1. Overlap on the same day, in any store.
            if ($sameDay && $otherStart < $newEnd && $otherEnd > $newStart) {
                $errors[] = 'This employee already works '
                    . hhmm($other['start_time']) . '-' . hhmm($other['end_time'])
                    . ' on ' . day_label($date) . '.';
                continue;
            }

            // 2. A second store on the same day.
            if ($sameDay && (int) $other['store_id'] !== $storeId) {
                $errors[] = 'This employee is already scheduled in another store on '
                    . day_label($date) . '.';
                continue;
            }

            // 3. Rest time around the new shift.
            if ($otherEnd <= $newStart) {
                $hours = ($newStart->getTimestamp() - $otherEnd->getTimestamp()) / 3600;
                if ($hours < SHIFT_MIN_REST) {
                    $warnings[] = sprintf(
                        'Only %.1f hours of rest after the shift on %s (the rule is %d).',
                        $hours,
                        day_label($other['work_date']),
                        SHIFT_MIN_REST
                    );
                }
            } elseif ($otherStart >= $newEnd) {
                $hours = ($otherStart->getTimestamp() - $newEnd->getTimestamp()) / 3600;
                if ($hours < SHIFT_MIN_REST) {
                    $warnings[] = sprintf(
                        'Only %.1f hours of rest before the shift on %s (the rule is %d).',
                        $hours,
                        day_label($other['work_date']),
                        SHIFT_MIN_REST
                    );
                }
            }
        }

        // 4. A day the employee marked as unavailable and a manager approved.
        $unavailableDays = (int) $this->scalar(
            "SELECT COUNT(*) FROM availability
             WHERE user_id = :user AND work_date = :date
               AND is_available = 0 AND status = 'approved'",
            ['user' => $userId, 'date' => $date]
        );

        if ($unavailableDays > 0) {
            $warnings[] = 'The employee marked this day as unavailable.';
        }

        // 5. Enough people in that store on that day.
        $store = $this->stores->find($storeId);

        if ($store !== null) {
            $scheduled = $this->shifts->count_in_store($storeId, $date, $exceptId) + 1;

            if ($scheduled < (int) $store['min_staff']) {
                $warnings[] = sprintf(
                    '%s has %d of the %d people it wants scheduled on %s.',
                    $store['name'],
                    $scheduled,
                    (int) $store['min_staff'],
                    day_label($date)
                );
            }
        }

        return [
            'errors'   => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
        ];
    }
}
