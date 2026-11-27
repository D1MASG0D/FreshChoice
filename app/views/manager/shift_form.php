<?php
/** FR-04 (new shift) and FR-13 (change or remove a shift). */
$isEdit = $shiftId > 0;
$label  = $isEdit ? 'Change this shift' : 'Assign a shift';
?>

<div class="row">
    <div class="col-lg-6">
        <h1 class="h3 mb-3"><?= e($label) ?></h1>

        <?php if ($errors !== []): ?>
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">This shift was not saved:</div>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="<?= url('/shift/save') ?>">
                    <?= csrf_field() ?>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="shift_id" value="<?= (int) $shiftId ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="store_id">Store</label>
                        <select class="form-select" id="store_id" name="store_id" required>
                            <option value="">Choose a store</option>
                            <?php foreach ($stores as $store): ?>
                                <option value="<?= (int) $store['id'] ?>"
                                    <?= (int) $values['store_id'] === (int) $store['id'] ? 'selected' : '' ?>>
                                    <?= e($store['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="user_id">Employee</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose an employee</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?= (int) $employee['id'] ?>"
                                    <?= (int) $values['user_id'] === (int) $employee['id'] ? 'selected' : '' ?>>
                                    <?= e($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                    (<?= e($employee['store_name'] ?? 'no store') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">The home store is shown, but an employee may work in any store.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="work_date">Date</label>
                        <input class="form-control" type="date" id="work_date" name="work_date"
                               min="<?= e($minDate) ?>" max="<?= e($maxDate) ?>"
                               value="<?= e($values['work_date']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label" for="start_time">Start</label>
                            <input class="form-control" type="time" id="start_time" name="start_time"
                                   value="<?= e($values['start_time']) ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" for="end_time">End</label>
                            <input class="form-control" type="time" id="end_time" name="end_time"
                                   value="<?= e($values['end_time']) ?>" required>
                        </div>
                    </div>

                    <button class="btn btn-success" type="submit"><?= e($label) ?></button>
                    <a class="btn btn-outline-secondary" href="<?= url('/calendar?week=' . monday_of($values['work_date'])) ?>">Cancel</a>
                </form>
            </div>
        </div>

        <?php if ($isEdit): ?>
            <div class="card border-danger-subtle mt-3">
                <div class="card-body">
                    <h2 class="h6 text-danger">Remove this shift</h2>
                    <form method="post" action="<?= url('/shift/delete') ?>"
                          onsubmit="return confirm('Remove this shift from the schedule?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="shift_id" value="<?= (int) $shiftId ?>">

                        <div class="mb-2">
                            <label class="form-label small" for="delete_reason">Reason (optional)</label>
                            <input class="form-control form-control-sm" type="text" id="delete_reason"
                                   name="reason" maxlength="255" placeholder="For example: employee is sick">
                        </div>

                        <button class="btn btn-outline-danger btn-sm" type="submit">Remove shift</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
