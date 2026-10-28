# StudentHub Portal

Final integrated submission for WDF Practical 15 (D26CS127).

## Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin and import `code/schema.sql` (or `studenthub.sql`).
3. Open `http://localhost/studentHub/Practical-15/code/html/index.html`.
4. Register a student at `register.html`, then log in at `login.html`.

## Test accounts

- Admin: `admin@studenthub.edu` / `Admin@123`
- Student: create an account through the registration form.

## Main routes

- `authenticate.php`, `logout.php`: session login and logout
- `student_dashboard.php`: protected student dashboard
- `admin_dashboard.php`: protected admin dashboard, statistics, event Fetch CRUD, and audit log
- `api_events.php`: authenticated JSON GET/POST/PUT/DELETE endpoint

## Included syllabus coverage

Semantic accessible pages, responsive CSS Grid/Flexbox, JavaScript DOM/events/localStorage UI, registration validation, JSON Fetch search/filter/sort/pagination, PHP POST validation and CSV storage, MySQLi schema and prepared statements, password hashing, sessions, role checks, timeout, student/event management, JSON API CRUD, admin statistics, dynamic admin menu, audit log, testing and local deployment.

## Security and testing

Use valid and invalid registration inputs, duplicate email/username, invalid login, logout, expired session, direct unauthorized dashboard access, admin event add/update/delete, API error responses, and browser Network/Console checks. Do not commit real passwords or production database credentials.
