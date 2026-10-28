CREATE DATABASE IF NOT EXISTS cspit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cspit;

CREATE TABLE IF NOT EXISTS login_users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin','Student') NOT NULL DEFAULT 'Student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

DELETE FROM login_users WHERE role = 'Admin' AND email <> 'admin@cspit.edu';

INSERT INTO login_users (full_name, email, password_hash, role) VALUES
('StudentHub Administrator', 'admin@cspit.edu', '$2y$10$tZaMQKK.vnxKJ2JVFa7U5.dcd92S/Op.zWMrxRVOj36ZLenDlPjpm', 'Admin'),
('Niraj Visavadiya', 'niraj@cspit.edu', '$2y$10$tZaMQKK.vnxKJ2JVFa7U5.dcd92S/Op.zWMrxRVOj36ZLenDlPjpm', 'Student'),
('Aarav Patel', 'aarav@cspit.edu', '$2y$10$tZaMQKK.vnxKJ2JVFa7U5.dcd92S/Op.zWMrxRVOj36ZLenDlPjpm', 'Student'),
('Diya Shah', 'diya@cspit.edu', '$2y$10$tZaMQKK.vnxKJ2JVFa7U5.dcd92S/Op.zWMrxRVOj36ZLenDlPjpm', 'Student'),
('Rohan Mehta', 'rohan@cspit.edu', '$2y$10$tZaMQKK.vnxKJ2JVFa7U5.dcd92S/Op.zWMrxRVOj36ZLenDlPjpm', 'Student')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), role=VALUES(role);

-- Demo password for all seeded accounts: Student@123
