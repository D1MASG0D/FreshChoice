<?php /** Manager view: post a shift nobody is assigned to yet, and see all posts. */ ?>

<h1 class="h3 mb-3">Shift posts</h1>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Post an open shift</h2>

                <?php if ($errors !== []): ?>
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 small">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= url('/posts/save') ?>">
                    <?= csrf_field() ?>

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
                        <label class="form-label" for="work_date">Date</label>
                        <input class="form-control" type="date" id="work_date" name="work_date"
                               min="<?= e(date('Y-m-d')) ?>"
                               max="<?= e(date('Y-m-d', strtotime('+' . MAX_WEEKS_AHEAD . ' weeks'))) ?>"
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

                    <div class="mb-3">
                        <label class="form-label" for="needed">People needed</label>
                        <input class="form-control" type="number" id="needed" name="needed"
                               min="1" max="10" value="<?= (int) $values['needed'] ?>">
                    </div>

                    <button class="btn btn-success" type="submit">Post the shift</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3">Posted shifts</h2>

                <?php if ($posts === []): ?>
                    <p class="text-muted mb-0">No shifts posted yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Store</th>
                                <th>Needed</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= e(day_label($post['work_date'])) ?></td>
                                    <td><?= e(hhmm($post['start_time'])) ?>–<?= e(hhmm($post['end_time'])) ?></td>
                                    <td><?= e($post['store_name']) ?></td>
                                    <td><?= (int) $post['needed'] ?></td>
                                    <td>
                                        <span class="badge <?= $post['status'] === 'open' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                            <?= e($post['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
