<?php
declare(strict_types=1);

// Login and logout
final class AuthController
{
    // Show the login page
    public static function showLogin(PDO $pdo): void
    {
        if (Auth::check()) {
            redirect(Auth::homePage());
        }
        render('auth/login', ['email' => '', 'error' => null, 'flashes' => take_flashes()]);
    }

    // Check the login form
    public static function login(PDO $pdo): void
    {
        csrf_check();

        $email    = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        $user = ($email !== '' && $password !== '' && strlen($password) <= 1024)
            ? Auth::attempt($pdo, $email, $password)
            : null;

        if ($user === null) {

            render('auth/login', [
                'email'   => $email,
                'error'   => 'Incorrect email or password.',
                'flashes' => [],
            ], 401);
            return;
        }

        Auth::login($user);
        redirect(Auth::homePage());
    }

    // Log out
    public static function logout(PDO $pdo): void
    {
        if (!Auth::check()) {
            redirect('login');
        }
        csrf_check();
        Auth::logout();
        session_start();
        flash('success', 'You have been logged out.');
        redirect('login');
    }
}
