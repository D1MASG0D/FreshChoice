<?php
/**
 * PLACEHOLDER login page for FR-01. Person 4 replaces this file and
 * AuthController with the real version; the rest of the app only needs
 * $_SESSION['user_id'], ['role'] and ['name'] to be set.
 */
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm mt-5">
            <div class="card-body p-4">
                <h1 class="h4 mb-1 text-success"><?= APP_NAME ?></h1>
                <p class="text-muted small mb-4">Shift management for three stores</p>

                <form method="post" action="<?= url('/login') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="email">E-mail</label>
                        <input class="form-control" type="email" id="email" name="email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" type="password" id="password" name="password" required>
                    </div>

                    <button class="btn btn-success w-100" type="submit">Sign in</button>
                </form>
            </div>
        </div>

        <div class="card border-0 bg-white mt-3">
            <div class="card-body small text-muted">
                <div class="fw-semibold mb-2">Demo accounts (password: password123)</div>
                <div>Manager &mdash; manager@freshchoice.test</div>
                <div>Employee &mdash; lena@freshchoice.test</div>
            </div>
        </div>
    </div>
</div>
