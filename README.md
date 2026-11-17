# FreshChoice – Shift Management Web Application

A web application that replaces the single Excel file FreshChoice uses to schedule staff across its three supermarkets. Managers plan rosters for all locations from one place; employees check their shifts, request days off, change their availability and pick up posted shifts from their phone.

This repository holds the documentation for the project and, as it is built, the application itself. It started as an ICT project at Fontys: four weeks of analysis, advice, design and implementation for a supermarket with one flagship store and two smaller neighbourhood shops.

## The problem

The schedule for all three stores lives in one Excel file on one manager's computer. Staff see it as a printout, a photo in a group chat or a forwarded message. Days off, swaps and availability changes are asked for verbally, by WhatsApp or on paper.

That produces five concrete problems, which the whole project is measured against:

1. Managers cannot see the shifts of employees in the other stores.
2. Employees cannot request days off efficiently.
3. Mistakes in the schedule — the main problem: shifts are added and changed by hand every day.
4. No efficient way to hand a shift over to another employee.
5. No data on sickness and lateness.

The as-is process, with those problems numbered on the flows:

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

Out of scope for this release: the clock-in system (FR-21), payroll calculation, and automatic replacement suggestions. The full list with priorities is in [Analysis.md](Analysis.md), section 5.

## Technology

| Layer | Technology |
| --- | --- |
| Structure | HTML |
| Presentation | CSS with Bootstrap 5, themed through CSS variable overrides |
| Server-side | PHP 8: sessions, validation, business rules, page rendering |
| Client-side | JavaScript: form feedback, AJAX, polling |
| Data | MariaDB (MySQL-compatible), administered through phpMyAdmin |

A classic LAMP-style stack: free, documented, no build step, and hostable by FreshChoice itself. The reasoning, the alternatives and the risks are in [TechnologyChoice.md](TechnologyChoice.md).

## Documentation

| File | What it covers |
| --- | --- |
| [Analysis.md](Analysis.md) | Context, stakeholders, root causes, competitors, technology, requirements, MoSCoW and scope |
| [TechnologyChoice.md](TechnologyChoice.md) | Why this stack, what was rejected, how it covers the requirements |
| [DataFlowAndAlgorithms.md](DataFlowAndAlgorithms.md) | Data flows and the eleven algorithms behind login, shifts, requests, chat, reset and reporting |
| [DFD-level1.png](DFD-level1.png) | Level-1 data flow diagram: entities, five processes, nine data stores |
| [BPMN-as-is.png](BPMN-as-is.png) | As-is process, with the five problems annotated |
| Advice.pdf | The advice: three options compared, Solution C recommended, to-be process |
| PossibleSolution.pdf | The three options in full, with the comparison matrix |
| StakeholderAnalyses.pdf | Store management, supervisors, staff and customers |
| RootCause.pdf | Five whys, four root causes, consequences |
| CompetitorAnalyses.pdf | Dayforce, observed in a comparable two-location store |
| Design.pdf | Design deliverables and who owns each one |
| MoSCoW.pdf, SmartGoal.pdf, DotFramework.pdf, SDGs.pdf | Prioritisation, goals, research methods, sustainability |

## Planned application structure

Nothing is built in this repository yet. The agreed layout, so the code stays out of the web root except for what the browser needs:

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

## Running it locally

The application is not in the repository yet, so there is nothing to run. When it lands, it will need:

1. Apache with PHP 8 and MariaDB — XAMPP, or any LAMP install.
2. The project folder inside the web root, with `public/` as the document root.
3. The schema imported through phpMyAdmin, and the credentials set in `config/db.php`.

## Status

- Analysis: complete.
- Advice: complete, including the comparison of Excel, a dashboard and a custom application.
- Design: data flow, processes and backend algorithms documented; wireframes, ERD and RBAC matrix still open.
- Implementation: not started.

## Team

Built by the FreshChoice project team. The work split for the analysis phase is recorded in [Analysis.md](Analysis.md), section 7.
