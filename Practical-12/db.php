<?php

mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$pass = "";
$db = "student_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
mysqli_autocommit($conn, true);

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS event_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin') NOT NULL DEFAULT 'Admin'
) ENGINE=InnoDB");

$admin_hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$admin_statement = mysqli_prepare($conn, 'INSERT INTO event_admins (name, email, password_hash) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), role = \'Admin\'');
if ($admin_statement) {
    $admin_name = 'StudentHub Administrator';
    $admin_email = 'admin@studenthub.local';
    mysqli_stmt_bind_param($admin_statement, 'sss', $admin_name, $admin_email, $admin_hash);
    mysqli_stmt_execute($admin_statement);
    mysqli_stmt_close($admin_statement);
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    poster VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Upgrade the earlier event table without deleting existing records.
$has_title = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'title'");
$has_old_name = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'event_name'");
if ($has_title && mysqli_num_rows($has_title) === 0 && $has_old_name && mysqli_num_rows($has_old_name) > 0) {
    mysqli_query($conn, "ALTER TABLE events CHANGE event_name title VARCHAR(150) NOT NULL");
}
$has_poster = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'poster'");
$has_old_poster = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'poster_path'");
if ($has_poster && mysqli_num_rows($has_poster) === 0 && $has_old_poster && mysqli_num_rows($has_old_poster) > 0) {
    mysqli_query($conn, "ALTER TABLE events CHANGE poster_path poster VARCHAR(255) NULL");
}

// Practical 11 soft-delete support. This runs once and keeps existing rows.
$status_column = mysqli_query($conn, "SHOW COLUMNS FROM students LIKE 'status'");
if ($status_column && mysqli_num_rows($status_column) === 0) {
    mysqli_query($conn, "ALTER TABLE students ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
}

?>
