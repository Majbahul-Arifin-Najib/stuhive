# StuHive

**Everything our campus needs, in one hive.**

StuHive is a campus community platform for university students, faculty and administrators. It brings fifteen separate campus needs — lost property, club events, shared lecture notes, faculty consultations, study groups, a student marketplace, accommodation listings and a personal money manager — into a single application with one login and three role-aware dashboards.

Built with Laravel 13, Blade, Tailwind CSS v4 and MySQL.

---

## Table of contents

- [Overview](#overview)
- [Tech stack](#tech-stack)
- [Features](#features)
- [Roles and visibility](#roles-and-visibility)
- [Getting started](#getting-started)
- [Demo accounts](#demo-accounts)
- [Configuration](#configuration)
- [The AI expenditure summary](#the-ai-expenditure-summary)
- [Project structure](#project-structure)
- [Database schema](#database-schema)
- [Scheduled tasks](#scheduled-tasks)
- [Testing](#testing)
- [Development commands](#development-commands)
- [Security notes](#security-notes)

---

## Overview

Every user signs up as a **student**, a **faculty member** or an **admin**, and each role gets its own dashboard with a calendar, profile card and private notepad. What appears in the sidebar — and what a user is allowed to open — is driven entirely by their role.

Three ideas hold the application together:

**A post supertype.** Ten of the fifteen features are "a post with extra fields". They all share one `posts` table holding the author, body and timestamps, plus a per-feature detail table (`lost_found_posts`, `event_posts`, `marketplace_posts`, …) keyed on `post_id`. Reactions, comments and moderation are written once and work everywhere.

**One enum as the source of truth.** `App\Enums\PostType` knows every section's label, icon, URL, detail model, and — critically — whether it allows comments, allows reactions, is hidden from faculty, and who is allowed to post in it. The sidebar, the middleware, the policies and the Blade components all read from it, so a rule can only be defined in one place.

**A derived calendar.** Rather than copying event dates into a calendar table and risking drift, `CalendarService` builds each user's month at read time from the events they marked interest in, the published exam schedule, and their consultation bookings.

---

## Tech stack

| Layer | Choice |
|---|---|
| Runtime | PHP 8.4 |
| Framework | Laravel 13.22 |
| Database | MySQL |
| Templating | Blade (server rendered, no SPA) |
| Styling | Tailwind CSS v4 (CSS-first `@theme` config) |
| JavaScript | Vanilla, data-attribute driven — no framework |
| Build | Vite 8 |
| Testing | Pest 4 |
| Formatting | Laravel Pint |
| AI | Claude (Anthropic Messages API) — optional |

There is no frontend framework and no additional Composer package beyond what the Laravel skeleton ships with. Interactivity (mobile nav, dropdowns, dialogs, image previews, group chat polling) is a single ~280-line `resources/js/app.js` driven by `data-*` attributes, keeping Blade free of inline JavaScript.

---

## Features

### 1. Lost & Found
Students post items they have lost or found, with an optional photo and last-seen location. The author gets a **Found** button; tapping it removes the post from the public feed while the record stays in the database. Authors can review their own resolved items under a separate tab. Supports emoji reactions and comments.

### 2. Candid Sharing Wall
An image wall for everyday campus moments. Reactions only — the comment endpoint returns `403` by design. Hidden from faculty accounts.

### 3. Event Announcements
Clubs publish events with a date, time, venue and optional poster. Every post carries an **Interested** button; tapping it writes the event into the student's dashboard calendar and increments a public interest count. Tapping again removes it.

### 4. Calendar
A month grid on every dashboard, with previous/next/today navigation. Colour-coded by source: events (amber), exams (red), consultations (blue). A "Coming up" panel lists the next five entries chronologically.

### 5. Polls & Voting
Students post a yes/no question with an optional image and closing time. Results render as proportional bars with live counts and percentages. One vote per student, changeable until the poll closes. Hidden from faculty.

### 6. Resources Library
PDF note sharing tagged with a course code and faculty initial, searchable by either. Every file is downloadable and tracks a download counter. Reactions and comments enabled. Hidden from faculty.

### 7. Exam Schedule
A searchable table of course code, section, day, date, time and room. Search matches course, section, room or day, with a toggle for past exams. Published exams appear automatically on every student's calendar. Admin and faculty can add and remove entries; students have read-only access.

### 8. Campus Pets
Posts about the campus cats and dogs, with optional photo and where they were spotted. Reactions and comments enabled.

### 9. Consultation Hub
Faculty publish consultation slots with a course, date, time, room and capacity. Students book a slot and optionally state their topic. Faculty see the full applicant list per slot and a live `booked / capacity` count; full slots refuse further bookings. Faculty can **postpone** a slot to a new date and time with a reason, which notifies every booked student.

### 10. Course Discussion & Review
Students share experiences of a course and its faculty with an optional 1–5 star rating, searchable by course code or initial. Reactions and comments enabled. Hidden from faculty accounts.

### 11. Marketplace
Product listings with price, condition, contact number and photo. Reactions only — no comments. Sellers can mark an item sold, and sold items are hidden by default behind a toggle.

### 12. Study Group Finder
A student posts a course and a **Max Member** count. Others tap **Join**, which opens a shared group chat. The chat window opens on the first join and stays available for a configurable period (24 hours by default); once it closes, the chat disappears **and its messages are deleted from the database**. Capacity is enforced — once the group is full nobody else can join. Messages poll every four seconds; non-members receive `403` and expired chats return `410`. Hidden from faculty.

### 13. Accommodation
Room and flat listings with area, walking distance, phone number, optional rent and a photo of the property. Deliberately has **no comment section** — the phone number renders as a tap-to-call link. Searchable by area. Hidden from faculty.

### 14. Money Management
Students set a monthly budget and log expenses with a description, category, amount and date. The spend list shows both the **date and the day of the week**. A progress bar and category breakdown visualise where the money went, and a **"low balance"** warning fires once spending reaches 80% of the budget (configurable), escalating to an over-budget alert. Once a month has ended, an **AI review** summarises the month's expenditure and suggests where to save. Hidden from faculty.

### 15. Downloads and notifications
Every image attached to any post and every shared PDF can be downloaded through authorised, policy-checked endpoints — filenames are generated server-side, never taken from the browser. Students are notified when a new post is published, faculty are notified when a student books a consultation, and students are notified when a consultation they booked is rescheduled. A notification centre shows unread badges in the sidebar and topbar.

### Available to every role
A **take-note** notepad (create, edit inline, delete — private to the owner), a profile page with photo upload, and a hive-points counter that rewards posting, commenting and reuniting lost items.

---

## Roles and visibility

The specification hides several sections from faculty accounts. This is enforced in three places — middleware (`section:`), policies, and navigation — so a hidden section cannot be reached by typing its URL.

| Section | Student | Faculty | Admin |
|---|:---:|:---:|:---:|
| Dashboard, calendar, notes, notifications, profile | ✅ | ✅ | ✅ |
| Lost & Found | ✅ | 👁 | 👁 |
| Candid Sharing Wall | ✅ | ❌ | 👁 |
| Event Announcements | ✅ | 👁 | ✅ |
| Polls & Voting | ✅ | ❌ | 👁 |
| Resources Library | ✅ | ❌ | 👁 |
| Exam Schedule | 👁 | ✅ | ✅ |
| Campus Pets | ✅ | 👁 | 👁 |
| Consultation Hub | 📅 | ✅ | 👁 |
| Course Discussion & Review | ✅ | ❌ | 👁 |
| Marketplace | ✅ | 👁 | 👁 |
| Study Group Finder | ✅ | ❌ | 👁 |
| Accommodation | ✅ | ❌ | 👁 |
| Money Management | ✅ | ❌ | ✅ |
| Moderation | ❌ | ❌ | ✅ |

✅ full access &nbsp;·&nbsp; 👁 view only &nbsp;·&nbsp; 📅 can book &nbsp;·&nbsp; ❌ blocked (403)

Admins can delete **any post or poll at any time**. Deletion is a soft delete, so moderated content disappears from the frontend while the record remains in the database.

---

## Getting started

### Requirements

- PHP 8.4+
- Composer
- Node.js 20+ and npm
- MySQL 8 (or MariaDB 10.4+)

### Installation

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Point .env at our database
#    DB_DATABASE=stuhive_mysql
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Create the schema and load demo data
php artisan migrate --seed

# 5. Make uploaded images and PDFs publicly reachable
php artisan storage:link

# 6. Build the frontend
npm run build
```

### Running it

With **Laravel Herd**, the site is already served at `http://stuhive.test` — nothing else to start.

Otherwise:

```bash
composer run dev
```

That runs the PHP server, queue listener, log viewer and Vite dev server together on `http://localhost:8000`.

---

## Demo accounts

`php artisan migrate --seed` creates 16 users, 55 posts across every section, 18 exams, polls with votes, consultation bookings, and two months of spending history.

| Role | Email | Password |
|---|---|---|
| Student | `student@g.bracu.ac.bd` | `password` |
| Faculty | `rifat.ahmed@bracu.ac.bd` | `password` |
| Admin | `admin@g.bracu.ac.bd` | `password` |

All other seeded students share the same password.

---

## Configuration

Application behaviour lives in `config/stuhive.php` and is overridable from `.env`:

| Variable | Default | Purpose |
|---|---|---|
| `STUHIVE_CHAT_HOURS` | `24` | How long a study group chat stays open after the first join |
| `STUHIVE_LOW_BALANCE_THRESHOLD` | `0.8` | Fraction of budget that triggers the "low balance" warning |
| `STUHIVE_UPLOAD_DISK` | `public` | Filesystem disk for images and PDFs |
| `ANTHROPIC_API_KEY` | *(empty)* | Enables the Claude-powered monthly summary |
| `ANTHROPIC_MODEL` | `claude-opus-5` | Model used for the summary |

Also configurable in `config/stuhive.php`: reward points per action (post `5`, comment `2`, reuniting a lost item `10`) and upload limits (images 4 MB, PDFs 20 MB).

---

## The AI expenditure summary

At the end of a month, students can generate a review of where their money went and where they could save.

The feature is built against a `SpendingAdvisor` contract with two implementations:

- **`ClaudeSpendingAdvisor`** — sends the month's budget, category totals and full transaction list to the Claude Messages API and asks for a structured JSON response (a summary plus a list of savings tips).
- **`RuleBasedSpendingAdvisor`** — computes the same shape locally from simple arithmetic: totals, biggest category and its share, daily average, and targeted tips based on small-purchase frequency and budget overrun.

`AppServiceProvider` binds the Claude implementation when `ANTHROPIC_API_KEY` is set, and the rule-based one otherwise. **The Claude implementation additionally falls back to the local advisor on any failure** — network error, HTTP error, malformed payload, or a safety refusal — so the page never breaks. The stored summary records which produced it, and the UI labels it *Claude* or *Offline advisor*.

To enable it, add your key to `.env`:

```dotenv
ANTHROPIC_API_KEY=sk-ant-...
```

> The API call is made with Laravel's HTTP client rather than the official `anthropic-ai/sdk` package, to keep the project's dependency list unchanged. Swapping in the SDK only requires rewriting `ClaudeSpendingAdvisor::ask()`.

---

## Project structure

```
app/
├── Actions/              CreatePost, RegisterUser — discrete business operations
├── Console/Commands/     PurgeExpiredChats
├── Contracts/            SpendingAdvisor interface
├── Enums/                PostType (the feature rulebook), Role
├── Http/
│   ├── Controllers/      One per section + Auth/ and Admin/
│   │   └── Concerns/     ManagesPostSection — shared feed/guard/delete behaviour
│   ├── Middleware/       EnsureUserHasRole, EnsureSectionIsVisible
│   └── Requests/         Form requests with validation rules
├── Models/
│   └── Concerns/         BelongsToPost — shared detail-table behaviour
├── Notifications/        NewPostPublished, ConsultationBooked, ConsultationPostponed
├── Policies/             Post, Poll, Comment, Note
├── Services/
│   ├── Advisors/         ClaudeSpendingAdvisor, RuleBasedSpendingAdvisor
│   ├── BudgetService     Monthly budget maths and warnings
│   ├── CalendarService   Derives calendar entries at read time
│   ├── FileUploader      Safe uploads with generated filenames
│   └── StudyGroupChat    Join, send, expiry and purge logic
└── Support/              Navigation, NavItem, CalendarEntry, SpendingSummary

resources/views/
├── components/
│   ├── ui/               card, button, input, select, textarea, field, badge,
│   │                     avatar, alert, empty-state, page-header
│   ├── post/             card, reactions, comments, composer, image
│   ├── layouts/          app (sidebar shell), guest (split auth screen)
│   ├── calendar.blade    Month grid
│   ├── stat.blade        Dashboard metric tile
│   └── icon.blade        Inline SVG icon set
├── dashboard/            student, faculty, admin + shared partials
└── <one folder per section>
```

### Notable patterns

- **`ManagesPostSection`** — a trait giving every section controller its eager-loaded feed, a `guardType()` check so `/pets/{post}` can't delete a marketplace post (returns `404`), an author-role check, and a policy-checked `destroy()`.
- **`BelongsToPost`** — configures the `post_id` primary key on all ten detail models in one place.
- **`Navigation`** — builds the sidebar from `PostType`, filtered by role, so nav and access control can never disagree.
- **Blade components over includes** — every shared piece of UI takes explicit props and merges its attribute bag.

---

## Database schema

36 tables. The design keeps the supertype/subtype structure of the original ER model while modernising the storage decisions.

**Identity**

| Table | Purpose |
|---|---|
| `users` | Name, email, hashed password, `role`, photo path, hive points |
| `students` | `user_id` PK, student ID, department |
| `faculties` | `user_id` PK, initial, designation, desk number |

**Posts** — `posts` (supertype: author, `type`, content, soft deletes) plus one detail table each: `lost_found_posts`, `candid_posts`, `event_posts`, `note_posts`, `pet_posts`, `course_review_posts`, `marketplace_posts`, `accommodation_posts`, `consultation_posts`, `study_groups`.

**Interactions** — `comments`, `reactions`, `event_interests`, `consultation_bookings`, `study_group_members`, `study_group_messages`, `polls`, `poll_votes`.

**Standalone** — `exam_schedules`, `notes`, `budgets`, `expenses`, `expense_summaries`, `notifications`.

### Changes from the original SQL schema

The supplied `StuHive.sql` was used as the structural blueprint. Five things were changed:

| Original | Now | Why |
|---|---|---|
| `Password varchar(8)` | Bcrypt hash | The original stored passwords in plaintext, capped at 8 characters |
| `longblob` images and PDFs | File paths on the `public` disk | Blobs pull megabytes through MySQL on every feed query and make caching impossible |
| `Students_Comments_on` PK `(User_ID, Post_ID)` | Own `id` column | The composite key allowed only **one comment per person per post** |
| `Faculty_Provide_Consultation` join table | Removed | Redundant — `posts.user_id` already identifies the faculty who owns the slot |
| `Money_Management` (one flat table) | `budgets` + `expenses` | Budget and spending are different cardinalities; splitting them is what makes the monthly rollup and category breakdown work |

Every table has indexes on the columns actually used in `WHERE`, `ORDER BY` and `JOIN` clauses, and foreign keys cascade on delete.

---

## Scheduled tasks

```bash
php artisan stuhive:purge-expired-chats
```

Deletes messages belonging to study group chats whose window has closed. Registered in `routes/console.php` to run **every fifteen minutes**. Expired chats are also purged on read, so the feature stays correct even if the scheduler is not running — the command exists to reclaim storage promptly.

In development, `composer run dev` starts the queue listener. In production, run the scheduler:

```bash
* * * * * cd /path/to/stuhive && php artisan schedule:run >> /dev/null 2>&1
```

---

## Testing

```bash
php artisan test              # full suite
php artisan test --compact    # condensed output
php artisan test --filter=StudyGroup
```

**97 tests, 286 assertions**, running against an in-memory SQLite database.

| Suite | Covers |
|---|---|
| `AuthTest` | Registration per role, role-specific field validation, login, logout, guest redirects |
| `RoleVisibilityTest` | Every faculty-hidden section returns 403; students reach all sections; sidebar filtering |
| `LostFoundTest` | Posting with a photo, the Found button hiding the post while keeping the row, author-only permission, reactions and comments, comment-free sections |
| `EventCalendarTest` | Interest toggling on/off, exams syncing to calendars, exam search, exam-management permissions |
| `ConsultationTest` | Slot publishing, booking with notification, capacity limits, applicant lists, postponement notifying students, cross-faculty permission |
| `StudyGroupTest` | Creation, chat opening on first join, timer not restarting, capacity, member-only access, expiry deleting messages, the purge command |
| `MoneyManagementTest` | Budgets, expense logging, low-balance and over-budget warnings, month-end gating, Claude summary via faked HTTP, fallback on error and on refusal, ownership checks |
| `ResourceLibraryTest` | PDF upload and validation, download with counter increment, search, faculty exclusion, image downloads |
| `ModerationTest` | Admin deleting any post or poll, soft-delete retention, author permissions, cross-section route guards |
| `NotificationTest` | New-post notifications reaching students but not the author or faculty, read state |
| `PollAndNoteTest` | Poll publishing, vote counting and changing, closed polls, notes CRUD for all three roles, note privacy |
| `SmokeTest` | Renders every page for every role against the full seeded dataset, plus calendar month paging |

---

## Development commands

```bash
composer run dev            # server + queue + logs + vite
npm run dev                 # vite only, with hot reload
npm run build               # production assets

php artisan migrate:fresh --seed   # rebuild and reseed the database
php artisan route:list --except-vendor

vendor/bin/pint             # format all PHP
vendor/bin/pint --dirty     # format changed files only
```

If a frontend change is not showing up, run `npm run build` (or keep `npm run dev` running).

---

## Security notes

- Passwords are bcrypt-hashed; the `password` cast handles it automatically.
- Every model declares an explicit fillable allowlist — no `$guarded = []`.
- All destructive routes go through policies (`PostPolicy`, `PollPolicy`, `CommentPolicy`, `NotePolicy`).
- Uploads validate MIME type and size, and are stored with **generated filenames** — browser-supplied names are never trusted.
- Download endpoints are authorisation-checked and stream from disk rather than exposing storage paths.
- Login is rate-limited to 6 attempts per minute.
- All output is escaped through `{{ }}`; the chat renderer builds DOM nodes with `textContent` rather than `innerHTML`.
- Admin deletes are soft deletes, preserving an audit trail.

### One thing to change before production

The registration form currently lets anyone choose the **Admin** role, because the specification states that a user can sign in as a student, faculty or admin. For a real deployment, restrict this — require an invite code, or seed admins manually and remove the option from the form.

---

## License

Built on the [Laravel framework](https://laravel.com), which is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
