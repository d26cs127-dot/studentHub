USE cspit;

ALTER TABLE students
    ADD COLUMN username VARCHAR(50) NULL AFTER email,
    ADD COLUMN password_hash VARCHAR(255) NULL AFTER username;

ALTER TABLE students
    ADD UNIQUE KEY uq_students_email (email),
    ADD UNIQUE KEY uq_students_username (username);
