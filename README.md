# FreshChoice – Shift Management Web Application

A web application that replaces the single Excel file FreshChoice uses to schedule staff across its three supermarkets. Managers plan rosters for all three locations from one place; employees check their shifts, request days off, change their availability and pick up posted shifts from their phone.

It is built as an ICT project at Fontys, following the full cycle of analysis, advice, design and implementation for a supermarket with one flagship store and two smaller neighbourhood shops.

## The problem

The schedule for all three stores lives in one Excel file on one manager's computer. Staff see it as a printout, a photo in a group chat or a forwarded message. Days off, swaps and availability changes are asked for verbally, by WhatsApp or on paper.

That produces five problems, which the whole project is measured against:

1. Managers cannot see the shifts of employees in the other stores.
2. Employees cannot request days off efficiently.
3. Mistakes in the schedule — the main problem: shifts are added and changed by hand every day.
4. No efficient way to hand a shift over to another employee.
5. No data on sickness and lateness.

The current process, with those problems numbered on the flows:

![As-is shift management process](BPMN-as-is.png)

## What it does

Two portals, one database:

- **Manager portal** — assign and change shifts for all three stores, see coverage per store, approve or decline requests and availability changes, create employee accounts, and read statistics on lateness, sickness and contract hours.
- **Staff portal** — mobile-first view of own shifts, day-off requests with a status, availability changes, posted shifts to claim, and group chat with the team.

Before anything is published, the schedule is checked automatically: overlapping shifts, a second shift at another store, too little rest between two shifts, availability the employee has not marked, and shifts left without enough staff. Data problems block the change; judgement calls warn the manager, who can override them with a reason.

## Scope

Twenty-one functional requirements, prioritised with MoSCoW: 7 Must, 7 Should, 6 Could, 1 Won't. The first version is the Must list.

| ID | Requirement |
| --- | --- |
| FR-01 | Different login for employees and managers |
| FR-03 | Employee can view their schedule |
| FR-04 | Manager can assign shifts |
| FR-05 | Manager can see information for all three stores |
| FR-12 | Manager can create accounts in the app for employees |
| FR-13 | Manager can manually change the schedule |
| FR-14 | Manager can approve requests and availability changes |

Out of scope for this release: the clock-in system (FR-21), payroll calculation, and automatic replacement suggestions. The Should items — day-off requests, shift posts, availability changes, chat, password reset — follow once the Must list works, and the Could items last.

## Technology

| Layer | Technology |
| --- | --- |
| Structure | HTML |
| Presentation | CSS with Bootstrap 5, themed through CSS variable overrides |
| Server-side | PHP 8: sessions, validation, business rules, page rendering |
| Client-side | JavaScript: form feedback, AJAX, polling |
| Data | MariaDB (MySQL-compatible), administered through phpMyAdmin |

A classic LAMP-style stack: free, documented, no build step, and hostable by FreshChoice itself. PHP renders the pages and enforces the rules before anything reaches the browser, and MariaDB keeps users, stores, shifts, requests and attendance in one place instead of three spreadsheets.

There is no WebSocket server, so chat and alerts are polled every few seconds. Delivery is near-real-time rather than instant, and the last fifty messages are rendered on page load so the conversation is always readable.

## Planned application structure

Nothing is built in this repository yet. The agreed layout keeps code out of the web root except for what the browser needs:

```
FreshChoice/
├── config/
│   └── db.php              # database connection and global constants
├── public/                 # the only folder the browser can reach
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── index.php           # single entry point
│   └── .htaccess
├── app/
│   ├── controllers/        # request handling
│   ├── models/             # SQL and data access
│   └── views/              # templates, with partials/
└── README.md
```

## Database & Local Setup

The database schema (`database.sql`) and PDO connection (`config/db.php`) are now available in the repository. To run the database locally:

1. **Start XAMPP:** Open the XAMPP Control Panel and start **Apache** and **MySQL** (MariaDB).
2. **Import the Schema:**
   - Open `http://localhost/phpmyadmin` in your browser.
   - Click **Import**, select `database.sql` from the root folder of this repository, and click **Import / Go**.
   - This creates the `freshchoice_db` database with all 7 tables (`store`, `user`, `shift`, `request`, `availability`, `attendance`, `shift_post`) and sample test data (3 stores, 1 manager, 3 employees, sample shifts, and 1 open shift post).
3. **Database Connection (`config/db.php`):**
   - Configured for local XAMPP defaults (`host: localhost`, `dbname: freshchoice_db`, `user: root`, `password: ""`).
   - Include it in backend PHP files using `require_once __DIR__ . '/../config/db.php';` to access the `$pdo` instance (configured with `ATTR_EMULATE_PREPARES => false` for prepared statement security).

## Status
- **Analysis:** Complete — problem, stakeholders, competitors, requirements and scope.
- **Advice:** Complete — Excel, a dashboard and a custom application compared, the custom application chosen.
- **Design:** Data flows, processes, backend algorithms, ERD, and relational database schema complete; wireframes and role matrix in progress.
- **Implementation:** Database schema (`database.sql`) and PDO connection (`config/db.php`) complete; application features in progress.

