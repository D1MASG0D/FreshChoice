<?php
declare(strict_types=1);

// Pages for managers
final class ManagerController
{

    // Show the shifts of all stores
    public static function shifts(PDO $pdo): void
    {
        Auth::requireRole('manager');

        [$from, $to] = date_range_from_request();
        $storeId = filter_var($_GET['store'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $storeId = $storeId === false ? null : $storeId;

        $shifts = ShiftModel::forRange($pdo, $from, $to, $storeId);

        if (($_GET['format'] ?? '') === 'json') {
            json_response(['from' => $from, 'to' => $to, 'store_id' => $storeId, 'shifts' => $shifts]);
        }

        $editing = null;
        $editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($editId !== false && $editId !== null) {
            $editing = ShiftModel::find($pdo, $editId);
        }

        render('manager/shifts', [
            'user'      => Auth::user(),
            'shifts'    => $shifts,
            'stores'    => StoreModel::all($pdo),
            'people'    => UserModel::listSchedulable($pdo),
            'from'      => $from,
            'to'        => $to,
            'storeId'   => $storeId,
            'editing'   => $editing,
            'flashes'   => take_flashes(),
        ]);
    }

    // Add a new shift
    public static function shiftCreate(PDO $pdo): void
    {
        Auth::requireRole('manager');
        csrf_check();

        [$id, $errors] = ShiftModel::create($pdo, $_POST);
        self::shiftResult($id, $errors, 'Shift assigned.');
    }

    // Change a shift
    public static function shiftUpdate(PDO $pdo): void
    {
        Auth::requireRole('manager');
        csrf_check();

        $shiftId = filter_var($_POST['shift_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($shiftId === false) {
            flash('error', 'Unknown shift.');
            redirect('manager-shifts');
        }
        [$id, $errors] = ShiftModel::update($pdo, $shiftId, $_POST);
        self::shiftResult($id, $errors, 'Shift updated.', ['edit' => $shiftId]);
    }

    // Remove a shift
    public static function shiftDelete(PDO $pdo): void
    {
        Auth::requireRole('manager');
        csrf_check();

        $shiftId = filter_var($_POST['shift_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($shiftId !== false && ShiftModel::delete($pdo, $shiftId)) {
            flash('success', 'Shift removed.');
        } else {
            flash('error', 'That shift no longer exists.');
        }
        redirect('manager-shifts');
    }

    // Show a message and go back
    private static function shiftResult(?int $id, array $errors, string $okMsg, array $onErrorParams = []): never
    {
        if ($id === null) {
            foreach ($errors as $e) {
                flash('error', $e);
            }
            redirect('manager-shifts', $onErrorParams);
        }
        flash('success', $okMsg);
        redirect('manager-shifts');
    }

    // Show the list of accounts and the new account form
    public static function accounts(PDO $pdo): void
    {
        Auth::requireRole('manager');
        render('manager/accounts', [
            'user'    => Auth::user(),
            'users'   => UserModel::listAll($pdo),
            'stores'  => StoreModel::all($pdo),
            'old'     => $_SESSION['old'] ?? [],
            'flashes' => take_flashes(),
        ]);
        unset($_SESSION['old']);
    }

    // Make a new account
    public static function accountCreate(PDO $pdo): void
    {
        Auth::requireRole('manager');
        csrf_check();

        $str = fn(string $k): string => is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : '';
        $first   = $str('first_name');
        $last    = $str('last_name');
        $email   = strtolower($str('email'));
        $phone   = $str('phone_number');
        $phone   = $phone === '' ? null : $phone;
        $role    = $str('role');
        $storeId = filter_var($_POST['store_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pass    = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        $errors = [];
        if ($first === '' || mb_strlen($first) > 50) { $errors[] = 'First name is required (max 50 characters).'; }
        if ($last === ''  || mb_strlen($last) > 50)  { $errors[] = 'Last name is required (max 50 characters).'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $errors[] = 'Enter a valid email address (max 100 characters).';
        }
        if ($phone !== null && !preg_match('/^\+?[0-9 \-]{6,20}$/', $phone)) {
            $errors[] = 'Enter a valid phone number (digits, spaces, dashes, optional +; max 20).';
        }
        if (!in_array($role, ['employee', 'manager'], true)) { $errors[] = 'Choose a role.'; }
        if ($storeId === false || !StoreModel::exists($pdo, $storeId)) { $errors[] = 'Choose a store.'; }

        if (strlen($pass) < 8 || strlen($pass) > 72 || str_contains($pass, "\0")) {
            $errors[] = 'Password must be 8 to 72 characters.';
        }
        if (!$errors && UserModel::emailExists($pdo, $email)) { $errors[] = 'An account with that email already exists.'; }

        if (!$errors) {
            try {
                UserModel::create($pdo, $storeId, $role, $first, $last, $email, $phone, password_hash($pass, PASSWORD_DEFAULT));
            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $errors[] = 'An account with that email already exists.';
                } else {
                    error_log('accountCreate failed: ' . $e->getMessage());
                    $errors[] = 'Could not create the account. Please try again.';
                }
            }
        }

        if ($errors) {
            foreach ($errors as $e) { flash('error', $e); }

            $_SESSION['old'] = compact('first', 'last', 'email', 'phone', 'role') + ['store_id' => $storeId];
            redirect('manager-accounts');
        }

        flash('success', "Account created for $first $last.");
        redirect('manager-accounts');
    }
}
