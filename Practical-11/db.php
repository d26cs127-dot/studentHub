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

// Practical 11 soft-delete support. This runs once and keeps existing rows.
$status_column = mysqli_query($conn, "SHOW COLUMNS FROM students LIKE 'status'");
if ($status_column && mysqli_num_rows($status_column) === 0) {
    mysqli_query($conn, "ALTER TABLE students ADD COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
}

?>
