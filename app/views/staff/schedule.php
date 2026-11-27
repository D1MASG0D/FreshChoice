<?php /** FR-03: the signed-in employee's own shifts for one week. */ ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h3 mb-0">My schedule</h1>
        <div class="text-muted small">
            Week of <?= e(day_label($days[0])) ?> &ndash; <?= e(day_label($days[6])) ?>
        </div>
    </div>

    <div class="d-flex gap-2 week-nav">
        <a class="btn btn-outline-success btn-sm" href="<?= url('/my-schedule?week=' . $previous) ?>">&larr; Previous</a>
        <a class="btn btn-outline-success btn-sm" href="<?= url('/my-schedule?week=' . $next) ?>">Next &rarr;</a>
    </div>
</div>

<div class="alert alert-success d-flex justify-content-between">
    <span>Hours this week</span>
    <strong><?= e(number_format($totalHours, 1)) ?> h</strong>
</div>

<?php if ($shifts === []): ?>
    <div class="card">
        <div class="card-body text-muted">No shifts scheduled for you this week.</div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($shifts as $shift): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card day-card h-100">
                    <div class="card-body">
                        <div class="text-muted small"><?= e(day_label($shift['work_date'])) ?></div>
                        <div class="h5 mb-1"><?= e(hhmm($shift['start_time'])) ?>–<?= e(hhmm($shift['end_time'])) ?></div>
                        <div class="text-muted small">Store #<?= (int) $shift['store_id'] ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
