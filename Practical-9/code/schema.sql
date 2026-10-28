CREATE DATABASE IF NOT EXISTS cspit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cspit;

CREATE TABLE IF NOT EXISTS students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(50) NULL,
    password_hash VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(100),
    event_date DATE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    event_id INT,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id)
) ENGINE=InnoDB;

SET @has_username = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cspit' AND TABLE_NAME = 'students' AND COLUMN_NAME = 'username');
SET @sql_username = IF(@has_username = 0, 'ALTER TABLE students ADD COLUMN username VARCHAR(50) NULL AFTER email', 'SELECT 1');
PREPARE stmt_username FROM @sql_username;
EXECUTE stmt_username;
DEALLOCATE PREPARE stmt_username;

SET @has_password_hash = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'cspit' AND TABLE_NAME = 'students' AND COLUMN_NAME = 'password_hash');
SET @sql_password_hash = IF(@has_password_hash = 0, 'ALTER TABLE students ADD COLUMN password_hash VARCHAR(255) NULL AFTER username', 'SELECT 1');
PREPARE stmt_password_hash FROM @sql_password_hash;
EXECUTE stmt_password_hash;
DEALLOCATE PREPARE stmt_password_hash;

SET @has_email_index = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'cspit' AND TABLE_NAME = 'students' AND INDEX_NAME = 'uq_students_email');
SET @sql_email_index = IF(@has_email_index = 0, 'ALTER TABLE students ADD UNIQUE KEY uq_students_email (email)', 'SELECT 1');
PREPARE stmt_email_index FROM @sql_email_index;
EXECUTE stmt_email_index;
DEALLOCATE PREPARE stmt_email_index;

SET @has_username_index = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = 'cspit' AND TABLE_NAME = 'students' AND INDEX_NAME = 'uq_students_username');
SET @sql_username_index = IF(@has_username_index = 0, 'ALTER TABLE students ADD UNIQUE KEY uq_students_username (username)', 'SELECT 1');
PREPARE stmt_username_index FROM @sql_username_index;
EXECUTE stmt_username_index;
DEALLOCATE PREPARE stmt_username_index;

INSERT INTO students (full_name, email)
SELECT 'Aarav Patel', 'aarav@example.com'
WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = 'aarav@example.com');
INSERT INTO students (full_name, email)
SELECT 'Diya Shah', 'diya@example.com'
WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = 'diya@example.com');
INSERT INTO students (full_name, email)
SELECT 'Rohan Mehta', 'rohan@example.com'
WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = 'rohan@example.com');

INSERT INTO events (event_name, event_date)
SELECT 'Web Development Workshop', '2026-11-05'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE event_name = 'Web Development Workshop' AND event_date = '2026-11-05');
INSERT INTO events (event_name, event_date)
SELECT 'Coding Competition', '2026-11-12'
WHERE NOT EXISTS (SELECT 1 FROM events WHERE event_name = 'Coding Competition' AND event_date = '2026-11-12');

INSERT INTO registrations (student_id, event_id)
SELECT students.student_id, events.event_id FROM students CROSS JOIN events
WHERE students.email = 'aarav@example.com' AND events.event_name = 'Web Development Workshop' AND events.event_date = '2026-11-05'
AND NOT EXISTS (SELECT 1 FROM registrations r WHERE r.student_id = students.student_id AND r.event_id = events.event_id);
INSERT INTO registrations (student_id, event_id)
SELECT students.student_id, events.event_id FROM students CROSS JOIN events
WHERE students.email = 'diya@example.com' AND events.event_name = 'Web Development Workshop' AND events.event_date = '2026-11-05'
AND NOT EXISTS (SELECT 1 FROM registrations r WHERE r.student_id = students.student_id AND r.event_id = events.event_id);
INSERT INTO registrations (student_id, event_id)
SELECT students.student_id, events.event_id FROM students CROSS JOIN events
WHERE students.email = 'rohan@example.com' AND events.event_name = 'Coding Competition' AND events.event_date = '2026-11-12'
AND NOT EXISTS (SELECT 1 FROM registrations r WHERE r.student_id = students.student_id AND r.event_id = events.event_id);
