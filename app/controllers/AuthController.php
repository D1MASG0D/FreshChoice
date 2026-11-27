<?php
/**
 * Signing in and out.
 *
 * PLACEHOLDER for FR-01. Person 4 owns the real login page: this file is
 * deliberately the only place that touches the login form, and the rest of
 * the app only relies on three session values:
 *
 *     $_SESSION['user_id'], $_SESSION['role'], $_SESSION['name']
 *
 * To swap in the real page, replace this controller and the login view.
 * Nothing else in the scheduling code needs to change.
 */
class AuthController
{
    private User $users;

    public function __construct(PDO $db)
    {
        $this->users = new User($db);
    }

    /** Send each role to the page it starts on. */
    public function home(): void
    {
        if (user_id() === null) {
            redirect('/login');
        }

        redirect(is_manager() ? '/calendar' : '/my-schedule');
    }

    /** Show the sign-in form. */
    public function showLogin(): void
    {
        if (user_id() !== null) {
            redirect(is_manager() ? '/calendar' : '/my-schedule');
        }

        view('auth/login', [], 'Sign in');
    }

    /** Check the e-mail and password, then start the session. */
    public function login(): void
    {
        check_csrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->users->find_by_email($email);

        // The same message for a wrong e-mail and a wrong password, so the
        // page does not tell strangers which accounts exist.
        if ($user === null || ! password_verify($password, $user['password_hash'])) {
            flash('That e-mail and password do not match an account.', 'danger');
            redirect('/login');
        }

        // A fresh session id after signing in stops session fixation.
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['first_name'] . ' ' . $user['last_name'];

        redirect($user['role'] === 'manager' ? '/calendar' : '/my-schedule');
    }

    /** End the session. */
    public function logout(): void
    {
        check_csrf();

        $_SESSION = [];
        session_destroy();

        redirect('/login');
    }
}
