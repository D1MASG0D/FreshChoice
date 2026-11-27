<?php /** Open shifts in the employee's own store. */ ?>

<h1 class="h3 mb-1">Open shifts</h1>
<p class="text-muted">
    Shifts posted at <?= e($store['name'] ?? 'your store') ?> that nobody is assigned to yet.
</p>

<?php if ($posts === []): ?>
    <div class="card">
        <div class="card-body text-muted">There are no open shifts at the moment.</div>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card day-card h-100">
                    <div class="card-body">
                        <div class="text-muted small"><?= e(day_label($post['work_date'])) ?></div>
                        <div class="h5 mb-1"><?= e(hhmm($post['start_time'])) ?>–<?= e(hhmm($post['end_time'])) ?></div>
                        <div class="text-muted small mb-2"><?= e($post['store_name']) ?></div>
                        <span class="badge bg-success-subtle text-success">
                            <?= (int) $post['needed'] ?> <?= (int) $post['needed'] === 1 ? 'person needed' : 'people needed' ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
