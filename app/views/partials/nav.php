<?php if (user_id() !== null): ?>
    <nav class="navbar navbar-expand bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="<?= url('/') ?>">
                <?= APP_NAME ?>
            </a>

            <ul class="navbar-nav me-auto">
                <?php if (is_manager()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/calendar') ?>">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/shift/new') ?>">Assign a shift</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/posts') ?>">Shift posts</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/my-schedule') ?>">My schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/open-shifts') ?>">Open shifts</a></li>
                <?php endif; ?>
            </ul>

            <span class="text-muted small me-3">
                <?= e($_SESSION['name'] ?? '') ?> · <?= e(current_role() ?? '') ?>
            </span>

            <form method="post" action="<?= url('/logout') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-success" type="submit">Sign out</button>
            </form>
        </div>
    </nav>
<?php endif; ?>
