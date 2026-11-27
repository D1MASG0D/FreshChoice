<?php
/**
 * Open shifts: a manager posts a shift nobody is assigned to yet,
 * and employees see the open ones for their own store.
 */
class ShiftPostController
{
    private ShiftPost $posts;
    private Store $stores;
    private User $users;

    public function __construct(private PDO $db)
    {
        $this->posts  = new ShiftPost($db);
        $this->stores = new Store($db);
        $this->users  = new User($db);
    }

    /** Manager page: the posting form plus every post. */
    public function create(): void
    {
        $this->page([], []);
    }

    /** Save a post. */
    public function store(): void
    {
        check_csrf();

        $values = [
            'store_id'   => (int) ($_POST['store_id'] ?? 0),
            'work_date'  => trim($_POST['work_date'] ?? ''),
            'start_time' => trim($_POST['start_time'] ?? ''),
            'end_time'   => trim($_POST['end_time'] ?? ''),
            'needed'     => max(1, (int) ($_POST['needed'] ?? 1)),
        ];

        $errors = [];

        if ($this->stores->find($values['store_id']) === null) {
            $errors[] = 'Choose a store.';
        }

        $dateProblem = date_error($values['work_date']);
        if ($dateProblem !== null) {
            $errors[] = $dateProblem;
        }

        $timeProblem = time_error($values['start_time'], $values['end_time']);
        if ($timeProblem !== null) {
            $errors[] = $timeProblem;
        }

        if ($errors !== []) {
            $this->page($values, $errors);
            return;
        }

        $postId = $this->posts->create($values + ['created_by' => user_id()]);
        (new AuditLog($this->db))->write(user_id(), 'shift post created', 'shift_posts', $postId);

        flash('Open shift posted for ' . day_label($values['work_date']) . '.');
        redirect('/posts');
    }

    /** Staff page: open posts for the employee's own store. */
    public function index(): void
    {
        $user = $this->users->find(user_id());

        view('staff/posts', [
            'posts' => $this->posts->open_for_store((int) $user['store_id']),
            'store' => $this->stores->find((int) $user['store_id']),
        ], 'Open shifts');
    }

    /** The manager page, with the form values and any error list. */
    private function page(array $values, array $errors): void
    {
        $defaults = [
            'store_id'   => 0,
            'work_date'  => date('Y-m-d'),
            'start_time' => '09:00',
            'end_time'   => '17:00',
            'needed'     => 1,
        ];

        view('manager/shiftposts', [
            'stores' => $this->stores->all(),
            'posts'  => $this->posts->all(),
            'errors' => $errors,
            'values' => array_merge($defaults, $values),
        ], 'Shift posts');
    }
}
