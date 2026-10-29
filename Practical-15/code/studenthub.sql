CREATE DATABASE IF NOT EXISTS cspit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cspit;

CREATE TABLE IF NOT EXISTS students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_no VARCHAR(40) UNIQUE,
    full_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    username VARCHAR(50) NULL,
    password_hash VARCHAR(255) NULL,
    department VARCHAR(80) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'Student',
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    last_login DATETIME NULL,
    UNIQUE KEY uq_students_email (email),
    UNIQUE KEY uq_students_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    poster VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NULL,
    action VARCHAR(80) NOT NULL,
    details VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- Demo credentials used to test role-based login. Passwords are generated by db.php on first run.
INSERT INTO students (enrollment_no, full_name, email, username, password_hash, department, role, status)
SELECT 'ADMIN-001', 'StudentHub Administrator', 'admin@studenthub.edu', 'admin', '$2y$10$QZZui3hxz2e4pv4vm0AG1OH7cjZFN64gzW8eMVePf01rlZZTnxY4y', 'Administration', 'Admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = 'admin@studenthub.edu');
INSERT INTO students (enrollment_no, full_name, email, username, password_hash, department, role, status)
SELECT 'STU-001', 'Niraj Visavadiya', 'student@studenthub.edu', 'student', '$2y$10$TMMCvjgbM7PEKcDDnYmcw.QjFIs5tPC6MwNzdADc0uIeQfEgagDES', 'Computer Science', 'Student', 'active'
WHERE NOT EXISTS (SELECT 1 FROM students WHERE email = 'student@studenthub.edu');

CREATE TABLE IF NOT EXISTS users (user_id INT PRIMARY KEY AUTO_INCREMENT, student_id INT NULL, username VARCHAR(80) NOT NULL UNIQUE, email VARCHAR(120) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role VARCHAR(20) NOT NULL DEFAULT 'Student', status VARCHAR(20) NOT NULL DEFAULT 'active', last_login DATETIME NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS remember_tokens (token_id INT PRIMARY KEY AUTO_INCREMENT, student_id INT NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS contact_messages (message_id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS feedback (feedback_id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(100) NOT NULL, email VARCHAR(120) NOT NULL, rating TINYINT NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
ALTER TABLE registrations ADD UNIQUE KEY uq_registration_student_event (student_id,event_id);
DROP PROCEDURE IF EXISTS count_event_registrations;
DELIMITER $$
CREATE PROCEDURE count_event_registrations(IN selected_event INT)
BEGIN
SELECT COUNT(*) AS total FROM registrations WHERE event_id=selected_event;
END$$
DELIMITER ;
INSERT INTO users (student_id,username,email,password_hash,role,status)
SELECT student_id,username,email,password_hash,role,status FROM students s WHERE s.email IN ('admin@studenthub.edu','student@studenthub.edu') AND NOT EXISTS (SELECT 1 FROM users u WHERE u.email=s.email);
