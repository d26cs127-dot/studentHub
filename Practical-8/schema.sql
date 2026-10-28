CREATE DATABASE IF NOT EXISTS cspit;
USE cspit;

CREATE TABLE IF NOT EXISTS students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(100),
    event_date DATE
);

CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    event_id INT,
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);

INSERT INTO students (name, email) VALUES
('Aarav Patel', 'aarav@example.com'),
('Diya Shah', 'diya@example.com'),
('Rohan Mehta', 'rohan@example.com');

INSERT INTO events (event_name, event_date) VALUES
('Web Development Workshop', '2026-11-05'),
('Coding Competition', '2026-11-12');

INSERT INTO registrations (student_id, event_id) VALUES
(1, 1),
(2, 1),
(3, 2);
