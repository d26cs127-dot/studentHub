# StudentHub Portal

StudentHub is an academic portal for students and administrators. It contains public academic information pages, registration and login, student information, notices, events, and study material. Students can access their dashboard and profile after login. Administrators can view portal counts, review audit entries, manage events, and manage student records.

## Technologies used

- HTML5
- CSS3
- JavaScript ES6
- Tailwind CSS through the Tailwind CDN on the pages that include it
- PHP
- MySQL with MySQLi
- XAMPP

## Features

### Public pages

- Home page with a notification banner, content slider, modal, and FAQ data.
- About, registration, login, contact, feedback, FAQ, notice board, assignments, attendance, timetable, and study material pages.
- Public JSON data for events, students, and FAQs.

### Frontend

- Registration validation for name, email, username, mobile, course, year, gender, password, confirmation password, and terms.
- Password strength feedback and a Canvas CAPTCHA on the registration page.
- FAQ interaction, mobile navigation, modal interaction, slider controls, notification banner, and dark theme switcher.
- Theme preference stored in `localStorage`.
- Fetch-based loading of JSON records with search, filter, sort, pagination, loading states, and error states.
- Session-based navigation links loaded through `api/session.php`.

### Authentication

- Registration inserts student accounts into MySQL.
- Login accepts email or username.
- Passwords use `password_hash()` and `password_verify()`.
- Session regeneration occurs after successful login.
- Admin and student role redirects are implemented.
- Logout clears the session and redirects to the login page.
- A remember-me token is generated, hashed, stored in `remember_tokens`, written to a cookie, and restored on a later request before the token expires.

### Student management

- Admin-only student API for listing, searching, adding, updating, and soft-deleting students.
- Student search page with a debounced search field.
- Student add and edit controls are available on the admin student page.
- Deactivation uses `status='inactive'` instead of permanently deleting the row.

### Event management

- Admin-only event API for listing, adding, updating, and deleting events.
- Poster upload validation for JPG, PNG, and WebP files.
- Poster size limit of 2 MB.
- Uploaded posters are renamed to random filenames and stored in `uploads/posters/`.
- Admin event cards display uploaded posters.
- Event changes are written to the audit log.
- Logged-in students can register for events from `events.php`.

### Admin dashboard

- Database counts for students, events, and registrations.
- Recent audit log entries.
- Canvas chart of event registrations grouped by month.
- AJAX event creation and deletion without a page reload.
- Admin-only student management page.

### Security

- Session timeout after 1,800 seconds of inactivity.
- Role checks for admin pages and APIs.
- CSRF token endpoint and CSRF validation for login, registration, contact, and feedback forms.
- MySQLi prepared statements for user input in the PHP handlers and APIs.
- Output escaping with `htmlspecialchars()` in PHP-rendered values.
- Data-folder `.htaccess` protection for JSON and CSV files.
- Upload MIME-type and file-size checks.

## Folder structure

```text
Practical-15/
├── README.md                         Project documentation
└── code/
    ├── html/                         Website root used by Apache
    │   ├── css/                      Shared stylesheet
    │   ├── js/                       Shared JavaScript
    │   ├── data/                     JSON data, CSV storage, and .htaccess protection
    │   ├── uploads/posters/           Uploaded event posters
    │   ├── includes/                  Wrappers for shared auth and database files
    │   ├── api/                      Session and student JSON API helpers
    │   ├── sql/                      MySQL import file
    │   ├── storage/                   Registration CSV storage
    │   ├── images/                    Image asset folder
    │   ├── *.html                     Public and static portal pages
    │   └── *.php                      Protected pages and form/API handlers
    ├── schema.sql                     Database schema and seed data
    ├── studenthub.sql                 Database schema and seed data copy
    └── migration_registration_columns.sql  Registration-column migration
```

The active database connection is in `code/html/db.php`. `code/html/includes/db.php` loads that file with `require_once`.

## Pages and user flow

### Page access

| File | Type | Access |
|---|---|---|
| `index.html` | Public | Everyone |
| `about.html` | Public | Everyone |
| `assignments.html` | Public/static | Everyone |
| `attendance.html` | Public/static | Everyone |
| `contact.html` | Public | Everyone |
| `dashboard.html` | Static page | Everyone who can open the file; dynamic dashboard is `dashboard.php` |
| `events.php` | Protected | Logged-in users; lists database events and accepts registrations |
| `faq.html` | Public | Everyone |
| `feedback.html` | Public | Everyone |
| `login.html` | Public | Everyone |
| `notice-board.html` | Public/static | Everyone |
| `profile.html` | Static page | Everyone who can open the file; protected profile is `profile.php` |
| `register.html` | Public | Everyone |
| `study-material.html` | Public/static | Everyone who can open the file |
| `timetable.html` | Public/static | Everyone who can open the file |
| `admin.php` | Protected | Admin only |
| `admin_dashboard.php` | Protected | Admin only |
| `students.php` | Protected | Admin only |
| `events_manage.php` | Protected | Admin only; loads the admin event page |
| `ajax_crud.php` | Protected | Admin only; loads the student CRUD page |
| `dashboard.php` | Protected | Logged-in users; used as the student dashboard destination |
| `profile.php` | Protected | Logged-in users |
| `student_dashboard.php` | Protected | Logged-in users; legacy student dashboard page |
| `api_events.php` | Protected API | Admin only |
| `api/students.php` | Protected API | Admin only |
| `api/session.php` | Session API | Public request; returns the current session state |

### User flow

1. Open `index.html`.
2. Select a dashboard link or another protected destination.
3. If there is no active session, the protected PHP page redirects to `login.html`.
4. Submit the login form.
5. An administrator is redirected to `admin.php`.
6. A student is redirected to `dashboard.php`.
7. Select Logout to destroy the session and return to `login.html`.

## Setup instructions (XAMPP)

1. Install XAMPP.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Copy the project folder to the Apache `htdocs` directory, for example `D:\xampp\htdocs\studentHub\Practical-15`.
4. Open phpMyAdmin.
5. Import `code/html/sql/studenthub.sql`.
6. The SQL file creates and uses the database named `cspit`.
7. The file `code/html/includes/db.php` loads `code/html/db.php`. Edit the `new mysqli()` call in `code/html/db.php` if the database settings differ:

   ```php
   $conn = new mysqli('localhost', 'root', '', 'cspit');
   ```

   The values are host, username, password, and database name in that order.

8. Open:

   `http://localhost/studentHub/Practical-15/code/html/index.html`

## Test login credentials

| Role | Email | Password | Username |
|---|---|---|---|
| Admin | `admin@studenthub.edu` | `Admin@123` | `admin` |
| Student | `student@studenthub.edu` | `Student@123` | `student` |

The same demo accounts are also created by `code/html/db.php` if they do not already exist.

## Database

| Table | Purpose | Key columns |
|---|---|---|
| `students` | Student and login records | `student_id`, `enrollment_no`, `email`, `username`, `password_hash`, `role`, `status` |
| `events` | Event records and poster filenames | `event_id`, `event_name`, `event_date`, `poster` |
| `registrations` | Student event registrations | `registration_id`, `student_id`, `event_id` |
| `audit_logs` | Login, student, and event activity | `log_id`, `student_id`, `action`, `details`, `created_at` |
| `users` | Seeded user account copy | `user_id`, `student_id`, `username`, `email`, `password_hash`, `role`, `status` |
| `remember_tokens` | Hashed remember-me tokens | `token_id`, `student_id`, `token_hash`, `expires_at` |
| `contact_messages` | Contact message table defined by SQL | `message_id`, `name`, `email`, `message`, `created_at` |
| `feedback` | Feedback table defined by SQL | `feedback_id`, `name`, `email`, `rating`, `message`, `created_at` |

Main foreign key relationships in the SQL file:

- `registrations.student_id` references `students.student_id`.
- `registrations.event_id` references `events.event_id`.

The SQL file also creates the `count_event_registrations` stored procedure.

## Testing guide

| Test | Steps | Expected result |
|---|---|---|
| Valid registration | Open `register.html`; submit valid values and accept terms. | A student row is inserted with a hashed password. |
| Invalid registration | Submit invalid name, email, mobile, password, or missing terms. | Client-side and server-side validation show errors. |
| Duplicate email | Register using an existing email or username. | Registration is rejected with a duplicate message. |
| Wrong password login | Use a valid email and an incorrect password. | Login redirects to `login.html?error=invalid`. |
| Correct admin login | Use the seeded admin credentials. | The session is created and the user is redirected to `admin.php`. |
| Correct student login | Use the seeded student credentials. | The session is created and the user is redirected to `dashboard.php`. |
| Role-based redirect | Log in once with each seeded role. | Admin and student destinations differ as described above. |
| Protected page while logged out | Open `admin.php`, `students.php`, or `profile.php` without logging in. | The page redirects to the login page. |
| Student opening an admin page | Log in as the student and open `students.php` or `api_events.php`. | The admin check rejects access. |
| Session timeout | Leave a logged-in session inactive for more than 1,800 seconds, then open a protected page. | The session is destroyed and the page redirects with a timeout error. |
| Remember-me token | Log in with Remember me selected, remove the session, and request a protected page before 30 days. | The stored token restores the active session. |
| Student add API | As admin, send a POST request to `api/students.php` with a name and valid email. | A student is inserted and a success JSON response is returned. |
| Student edit API | As admin, send a PUT request to `api/students.php` with `student_id`, name, email, and department. | The student is updated without a page reload. |
| Student delete API | As admin, select Deactivate in `students.php` or send DELETE to `api/students.php`. | The student status becomes inactive after confirmation in the page UI. |
| Student search | Open `students.php` and type a name, email, or course. | The debounced request refreshes the matching rows. |
| Student add/edit UI | Use Add student or Edit on `students.php`. | The row is added or updated through AJAX. |
| Event add | As admin, submit an event with a valid poster. | The event and renamed poster are stored, and the list refreshes. |
| Event edit API | As admin, send a PUT request to `api_events.php`. | The event name, date, and description are updated. |
| Event delete | Select Delete on an event card and confirm. | The event is deleted and its poster file is removed. |
| Student event registration | Log in as a student, open `events.php`, and select Register. | A row is inserted into `registrations`; repeat registration is rejected. |
| Invalid poster type | Upload a non-JPG, non-PNG, or non-WebP file. | The API rejects the upload. |
| Oversized poster | Upload a file larger than 2 MB. | The API rejects the upload. |
| AJAX event CRUD | Add or delete an event from the admin page. | The event list changes without a full page reload. |
| Admin dashboard counts | Log in as admin and open `admin.php`. | Student, event, and registration counts are loaded from MySQL. |
| Registration chart | Log in as admin and open `admin.php`. | A Canvas chart shows registrations grouped by month. |
| Audit log | Log in or change an event/student record as admin. | The activity is written to `audit_logs` where the handler calls `log_action()`. |
| Logout | Select Logout while logged in. | The session is destroyed and the login page opens. |

## Security measures

- [x] `password_hash()` is used when passwords are stored.
- [x] `password_verify()` is used during login.
- [x] MySQLi prepared statements are used for form and API user input.
- [x] `session_regenerate_id(true)` is called after login.
- [x] Session timeout is checked in `auth.php`.
- [x] Admin role checks protect admin pages and APIs.
- [x] CSRF tokens are generated and checked for login, registration, contact, and feedback forms.
- [x] Poster MIME type and size are validated before storage.
- [x] Poster filenames are randomly renamed.
- [x] PHP output values are escaped with `htmlspecialchars()` where rendered dynamically.
- [x] JSON and CSV files in `data/` are denied by `.htaccess`.
- [x] Remember-me values are random and only their SHA-256 hashes are stored.
- [x] Remember-me cookies are checked against stored hashes and expiry before restoring a session.

## Accessibility measures

- [x] Many pages use semantic `header`, `nav`, `main`, `section`, `article`, `aside`, and `footer` elements.
- [x] Form controls have labels on the registration, login, contact, feedback, and student search forms.
- [x] Dynamically displayed event posters receive generated alternative text.
- [x] Breadcrumb navigation is present on several existing public pages.
- [x] Several pages include a skip-to-content link.
- [x] FAQ controls use buttons and `aria-expanded` state.
- [x] Dynamic messages use status or alert roles on relevant pages.
- [x] Focus styles and keyboard-friendly buttons are defined in the shared CSS.
- [x] Breadcrumb navigation is present on the main existing public pages and generated for pages using the shared JavaScript.

## Practical mapping

| Practical | Files or folders | Implemented work |
|---|---|---|
| Practical 1 | `code/html/index.html` | Basic StudentHub page structure |
| Practical 2 | `code/html/about.html` | About page and semantic content |
| Practical 3 | `code/html/register.html`, `code/html/js/script.js` | Registration form and client-side validation |
| Practical 4 | `code/html/login.html`, `authenticate.php`, `logout.php` | Login and logout flow |
| Practical 5 | `code/html/dashboard.html`, `dashboard.php` | Student dashboard presentation |
| Practical 6 | `code/html/notice-board.html`, `data/events.json` | Notices and event data |
| Practical 7 | `code/html/faq.html`, `data/faqs.json` | FAQ page and JSON data |
| Practical 8 | `code/html/contact.html`, `contact-submit.php` | Contact form processing |
| Practical 9 | `code/html/feedback.html`, `feedback-submit.php` | Feedback form processing |
| Practical 10 | `code/html/assignments.html`, `attendance.html` | Assignment and attendance pages |
| Practical 11 | `code/html/timetable.html`, `study-material.html` | Timetable and study material pages |
| Practical 12 | `code/html/css/style.css`, `code/html/js/script.js` | Responsive layout and frontend interactions |
| Practical 13 | `code/html/db.php`, `code/html/sql/studenthub.sql` | MySQL database connection and schema |
| Practical 14 | `code/html/admin.php`, `admin_dashboard.php`, `api_events.php` | Admin dashboard and event management |
| Practical 15 | `code/html/api/`, `students.php`, `events.php`, `uploads/posters/` | JSON API, student management, event registration, and poster uploads |

## Author

Name: Niraj Visavadiya  
Program: CSE, CSPIT, CHARUSAT  
Subject: ITUE203 Web Development Frameworks  
Semester: 3  
Academic year: 2026-27
