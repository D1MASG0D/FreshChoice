<?php
declare(strict_types=1);

// Pages for employees
final class EmployeeController
{

    // Show the shifts of the person who is logged in
    public static function schedule(PDO $pdo): void
    {
        Auth::requireLogin();

        [$from, $to] = date_range_from_request();
        $shifts = ShiftModel::forUser($pdo, Auth::id(), $from, $to);

        if (($_GET['format'] ?? '') === 'json') {
            json_response(['from' => $from, 'to' => $to, 'shifts' => $shifts]);
        }

        render('employee/schedule', [
            'user'    => Auth::user(),
            'shifts'  => $shifts,
            'from'    => $from,
            'to'      => $to,
            'flashes' => take_flashes(),
        ]);
    }
}
