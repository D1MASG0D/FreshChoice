<?php
/**
 * Person 3's scheduling pages: the manager calendar, assigning a shift,
 * changing it, removing it, and the employee's own schedule.
 */
class ScheduleController
{
    private Shift $shifts;
    private Store $stores;
    private User $users;
    private ConflictChecker $checker;

    public function __construct(private PDO $db)
    {
        $this->shifts  = new Shift($db);
        $this->stores  = new Store($db);
        $this->users   = new User($db);
        $this->checker = new ConflictChecker($db);
    }

    /** FR-05: one week for all three stores, with an optional store filter. */
    public function calendar(): void
    {
        $monday  = $this->requested_week();
        $days    = week_days($monday);
        $storeId = (int) ($_GET['store'] ?? 0);

        $shifts = $this->shifts->for_week($monday, $storeId);

        // One bucket per day for the columns, plus a count per day and store
        // so the view can mark a day that is short of people.
        $grid     = [];
        $coverage = [];

        foreach ($days as $day) {
            $grid[$day] = [];
        }

        foreach ($shifts as $shift) {
            $day   = $shift['work_date'];
            $store = (int) $shift['store_id'];

            $grid[$day][] = $shift;
            $coverage[$day][$store] = ($coverage[$day][$store] ?? 0) + 1;
        }

        view('manager/calendar', [
            'monday'   => $monday,
            'days'     => $days,
            'grid'     => $grid,
            'coverage' => $coverage,
            'stores'   => $this->stores->all(),
            'storeId'  => $storeId,
            'previous' => date('Y-m-d', strtotime($monday . ' -7 days')),
            'next'     => date('Y-m-d', strtotime($monday . ' +7 days')),
        ], 'Schedule');
    }

    /** The empty assign form. */
    public function create(): void
    {
        $this->form([], [], 0);
    }

    /** The same form, filled with the shift being changed. */
    public function edit(): void
    {
        $shift = $this->shifts->find((int) ($_GET['id'] ?? 0));

        if ($shift === null) {
            flash('That shift does not exist any more.', 'danger');
            redirect('/calendar');
        }

        $this->form([
            'store_id'   => (int) $shift['store_id'],
            'user_id'    => (int) $shift['user_id'],
            'work_date'  => $shift['work_date'],
            'start_time' => hhmm($shift['start_time']),
            'end_time'   => hhmm($shift['end_time']),
        ], [], (int) $shift['id']);
    }

    /**
     * FR-04 and FR-13: save a new shift, or the change to an existing one.
     * One form posts here; a hidden shift_id decides which of the two it is.
     */
    public function store(): void
    {
        check_csrf();

        $shiftId = (int) ($_POST['shift_id'] ?? 0);

        if ($shiftId > 0 && $this->shifts->find($shiftId) === null) {
            flash('That shift does not exist any more.', 'danger');
            redirect('/calendar');
        }

        $this->save($shiftId);
    }

    /** FR-13: remove a shift. */
    public function destroy(): void
    {
        check_csrf();

        $id    = (int) ($_POST['shift_id'] ?? 0);
        $shift = $this->shifts->find($id);

        if ($shift === null) {
            flash('That shift does not exist any more.', 'danger');
            redirect('/calendar');
        }

        $this->with_transaction(function () use ($id) {
            $this->shifts->delete($id);
            (new AuditLog($this->db))->write(user_id(), 'shift deleted', 'shifts', $id, trim($_POST['reason'] ?? ''));
        });

        flash('Shift removed from ' . day_label($shift['work_date']) . '.');
        redirect('/calendar?week=' . monday_of($shift['work_date']));
    }

    /** FR-03: the signed-in employee's own week. */
    public function mySchedule(): void
    {
        $monday = $this->requested_week();

        // user_id() comes from the session, never from the URL, so nobody can
        // ask for somebody else's schedule.
        $shifts = $this->shifts->for_employee(user_id(), $monday, date('Y-m-d', strtotime($monday . ' +6 days')));

        $totalHours = 0;
        foreach ($shifts as $shift) {
            $totalHours += (strtotime($shift['end_time']) - strtotime($shift['start_time'])) / 3600;
        }

        view('staff/schedule', [
            'days'       => week_days($monday),
            'shifts'     => $shifts,
            'totalHours' => $totalHours,
            'previous'   => date('Y-m-d', strtotime($monday . ' -7 days')),
            'next'       => date('Y-m-d', strtotime($monday . ' +7 days')),
        ], 'My schedule');
    }

    /* ---------------------------------------------------------------------
     * Helpers used by more than one page above.
     * ------------------------------------------------------------------ */

    /**
     * Validate, run the five checks, then either show the form again,
     * ask for a confirmation, or write the shift.
     */
    private function save(int $shiftId): void
    {
        $input = [
            'store_id'   => (int) ($_POST['store_id'] ?? 0),
            'user_id'    => (int) ($_POST['user_id'] ?? 0),
            'work_date'  => trim($_POST['work_date'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? ''),
            'end_time'   => trim($_POST['end_time'] ?? ''),
        ];

        $errors   = $this->validate($input);
        $warnings = [];

        // The checks need valid data, so they only run once the form is clean.
        if ($errors === []) {
            $result   = $this->checker->check($input, $shiftId);
            $errors   = $result['errors'];
            $warnings = $result['warnings'];
        }

        // Something is wrong with the data: back to the form with the reasons.
        if ($errors !== []) {
            $this->form($input, $errors, $shiftId);
            return;
        }

        $reason    = trim($_POST['reason'] ?? '');
        $confirmed = ($_POST['confirmed'] ?? '') === '1';

        // Warnings the manager has not seen yet: show them once.
        if ($warnings !== [] && ! $confirmed) {
            $this->confirm($input, $warnings, $shiftId, $reason);
            return;
        }

        // Overriding a warning needs a written reason, kept in the audit log.
        if ($warnings !== [] && $reason === '') {
            $this->confirm($input, $warnings, $shiftId, $reason, 'Write a short reason for overriding the warning.');
            return;
        }

        $this->with_transaction(function () use ($input, $shiftId, $reason) {
            if ($shiftId > 0) {
                $this->shifts->update($shiftId, $input);
                $recordId = $shiftId;
                $action   = 'shift updated';
            } else {
                $recordId = $this->shifts->create($input);
                $action   = 'shift created';
            }

            (new AuditLog($this->db))->write(user_id(), $action, 'shifts', $recordId, $reason);
        });

        flash('Shift saved for ' . day_label($input['work_date']) . ', '
            . hhmm($input['start_time']) . '-' . hhmm($input['end_time']) . '.');

        redirect('/calendar?week=' . monday_of($input['work_date']));
    }

    /** Rules that never depend on other shifts. */
    private function validate(array $input): array
    {
        $errors = [];

        if ($this->stores->find($input['store_id']) === null) {
            $errors[] = 'Choose a store.';
        }

        $employee = $this->users->find($input['user_id']);
        if ($employee === null || $employee['role'] !== 'employee') {
            $errors[] = 'Choose an employee.';
        }

        $dateProblem = date_error($input['work_date']);
        if ($dateProblem !== null) {
            $errors[] = $dateProblem;
        }

        $timeProblem = time_error($input['start_time'], $input['end_time']);
        if ($timeProblem !== null) {
            $errors[] = $timeProblem;
        }

        return $errors;
    }

    /** Show the form with the values the manager typed and the error list. */
    private function form(array $values, array $errors, int $shiftId): void
    {
        $defaults = [
            'store_id'   => 0,
            'user_id'    => 0,
            'work_date'  => date('Y-m-d'),
            'start_time' => '09:00',
            'end_time'   => '17:00',
        ];

        view('manager/shift_form', [
            'values'    => array_merge($defaults, $values),
            'errors'    => $errors,
            'shiftId'   => $shiftId,
            'stores'    => $this->stores->all(),
            'employees' => $this->users->employees(),
            'minDate'   => date('Y-m-d'),
            'maxDate'   => date('Y-m-d', strtotime('+' . MAX_WEEKS_AHEAD . ' weeks')),
        ], $shiftId > 0 ? 'Change a shift' : 'Assign a shift');
    }

    /** Show the warnings and ask for a reason before saving anyway. */
    private function confirm(array $values, array $warnings, int $shiftId, string $reason, string $reasonError = ''): void
    {
        view('manager/shift_confirm', [
            'values'      => $values,
            'warnings'    => $warnings,
            'shiftId'     => $shiftId,
            'reason'      => $reason,
            'reasonError' => $reasonError,
        ], 'Check the warnings');
    }

    /**
     * The Monday the page asked for, kept inside the planning window.
     * A date in the URL is snapped to its Monday and clamped at both ends.
     */
    private function requested_week(): string
    {
        $requested = trim($_GET['week'] ?? '');
        $monday = $requested !== '' && strtotime($requested) !== false
            ? monday_of($requested)
            : monday_of('today');

        $firstMonday = monday_of('today');
        $lastMonday  = date('Y-m-d', strtotime($firstMonday . ' +' . MAX_WEEKS_AHEAD . ' weeks'));

        if ($monday < $firstMonday) {
            return $firstMonday;
        }

        if ($monday > $lastMonday) {
            return $lastMonday;
        }

        return $monday;
    }

    /**
     * Run the callback inside a transaction.
     * If anything fails, the shift and its audit row are both undone.
     */
    private function with_transaction(callable $work): void
    {
        $this->db->beginTransaction();

        try {
            $work();
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
}
