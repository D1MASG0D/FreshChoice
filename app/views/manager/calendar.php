<?php /** FR-05: one week, Monday to Sunday, all three stores or one. */ ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h3 mb-0">Schedule</h1>
        <div class="text-muted small week-label">
            Week of <?= e(day_label($days[0])) ?> &ndash; <?= e(day_label($days[6])) ?>
        </div>
    </div>

    <div class="d-flex gap-2 week-nav">
        <a class="btn btn-outline-success btn-sm" href="<?= url('/calendar?week=' . $previous . '&store=' . $storeId) ?>">&larr; Previous</a>
        <a class="btn btn-outline-success btn-sm" href="<?= url('/calendar?week=' . $next . '&store=' . $storeId) ?>">Next &rarr;</a>
        <a class="btn btn-success btn-sm" href="<?= url('/shift/new') ?>">Assign a shift</a>
    </div>
</div>

<form class="row g-2 align-items-end mb-3" method="get" action="<?= url('/calendar') ?>">
    <input type="hidden" name="week" value="<?= e($monday) ?>">
    <div class="col-auto">
        <label class="form-label small mb-1" for="store">Store</label>
        <select class="form-select form-select-sm" id="store" name="store" onchange="this.form.submit()">
            <option value="0">All three stores</option>
            <?php foreach ($stores as $store): ?>
                <option value="<?= (int) $store['id'] ?>" <?= $storeId === (int) $store['id'] ? 'selected' : '' ?>>
                    <?= e($store['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered bg-white calendar-table align-middle">
        <thead>
        <tr>
            <?php foreach ($days as $day): ?>
                <th class="text-center py-2">
                    <?= e(day_label($day)) ?>
                </th>
            <?php endforeach; ?>
        </tr>
        </thead>

        <tbody>
        <tr>
            <?php foreach ($days as $day): ?>
                <td>
                    <?php foreach ($grid[$day] as $shift): ?>
                        <div class="card shift-card mb-2">
                            <div class="card-body p-2">
                                <a class="shift-time text-decoration-none"
                                   href="<?= url('/shift/edit?id=' . (int) $shift['id']) ?>">
                                    <?= e(hhmm($shift['start_time'])) ?>–<?= e(hhmm($shift['end_time'])) ?>
                                </a>
                                <div><?= e($shift['first_name'] . ' ' . $shift['last_name']) ?></div>
                                <div class="shift-store"><?= e($shift['store_name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($grid[$day] === []): ?>
                        <div class="text-muted small mb-2">Nobody scheduled</div>
                    <?php endif; ?>

                    <?php foreach ($stores as $store): ?>
                        <?php
                        if ($storeId > 0 && $storeId !== (int) $store['id']) {
                            continue;
                        }

                        $scheduled = $coverage[$day][(int) $store['id']] ?? 0;
                        $wanted    = (int) $store['min_staff'];
                        ?>
                        <span class="badge <?= $scheduled < $wanted ? 'bg-warning text-dark' : 'bg-success-subtle text-success' ?> d-block mb-1">
                            <?= e($store['name']) ?>: <?= $scheduled ?>/<?= $wanted ?>
                        </span>
                    <?php endforeach; ?>
                </td>
            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
</div>

<p class="text-muted small mb-0">
    A yellow badge means the store has fewer people scheduled that day than it wants.
    Click a shift to change or remove it.
</p>
