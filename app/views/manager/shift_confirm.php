<?php
/**
 * Shown when the checks produced warnings. Nothing is saved yet: the
 * manager either goes back, or confirms with a written reason.
 */
$isEdit = $shiftId > 0;
?>

<div class="row">
    <div class="col-lg-6">
        <h1 class="h3 mb-3">Check before saving</h1>

        <div class="alert alert-warning">
            <div class="fw-semibold mb-1">The schedule check found:</div>
            <ul class="mb-0">
                <?php foreach ($warnings as $warning): ?>
                    <li><?= e($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="text-muted small">Shift to save</div>
                <div class="fw-semibold">
                    <?= e(day_label($values['work_date'])) ?>,
                    <?= e(hhmm($values['start_time'])) ?>–<?= e(hhmm($values['end_time'])) ?>
                </div>
            </div>
        </div>

        <form method="post" action="<?= url('/shift/save') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="confirmed" value="1">
            <input type="hidden" name="store_id" value="<?= (int) $values['store_id'] ?>">
            <input type="hidden" name="user_id" value="<?= (int) $values['user_id'] ?>">
            <input type="hidden" name="work_date" value="<?= e($values['work_date']) ?>">
            <input type="hidden" name="start_time" value="<?= e($values['start_time']) ?>">
            <input type="hidden" name="end_time" value="<?= e($values['end_time']) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="shift_id" value="<?= (int) $shiftId ?>">
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label" for="reason">Why is this acceptable?</label>
                <textarea class="form-control <?= $reasonError !== '' ? 'is-invalid' : '' ?>"
                          id="reason" name="reason" rows="2" maxlength="255"
                          placeholder="For example: employee agreed to the shorter break"><?= e($reason) ?></textarea>
                <?php if ($reasonError !== ''): ?>
                    <div class="invalid-feedback"><?= e($reasonError) ?></div>
                <?php endif; ?>
                <div class="form-text">The reason is stored in the audit log with the shift.</div>
            </div>

            <button class="btn btn-success" type="submit">Save anyway</button>
            <a class="btn btn-outline-secondary"
               href="<?= url($isEdit ? '/shift/edit?id=' . $shiftId : '/shift/new') ?>">Go back</a>
        </form>
    </div>
</div>
