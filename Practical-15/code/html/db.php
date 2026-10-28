<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli('localhost', 'root', '', 'cspit');
if ($conn->connect_errno) {
    http_response_code(500);
    exit('Database connection failed. Start MySQL in XAMPP and import schema.sql.');
}
$conn->set_charset('utf8mb4');

$migrations = [
    "ALTER TABLE students ADD COLUMN enrollment_no VARCHAR(40) NULL AFTER student_id",
    "ALTER TABLE students ADD COLUMN department VARCHAR(80) NULL AFTER password_hash",
    "ALTER TABLE students ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'Student' AFTER department",
    "ALTER TABLE students ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active' AFTER role",
    "ALTER TABLE events ADD COLUMN description TEXT NULL AFTER event_name",
    "ALTER TABLE events ADD COLUMN poster VARCHAR(255) NULL AFTER description",
];
foreach ($migrations as $sql) { @mysqli_query($conn, $sql); }
@mysqli_query($conn, "CREATE TABLE IF NOT EXISTS audit_logs (log_id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NULL, action VARCHAR(80) NOT NULL, details VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");

$adminEmail = 'admin@studenthub.edu';
$adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
$check = mysqli_prepare($conn, 'SELECT student_id FROM students WHERE email = ? LIMIT 1');
if ($check) {
    mysqli_stmt_bind_param($check, 's', $adminEmail);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    if (!$result || !$result->fetch_assoc()) {
        $insert = mysqli_prepare($conn, "INSERT INTO students (enrollment_no, full_name, email, username, password_hash, department, role, status) VALUES ('ADMIN-001','StudentHub Administrator',?,'admin',?,'Administration','Admin','active')");
        if ($insert) { mysqli_stmt_bind_param($insert, 'ss', $adminEmail, $adminHash); mysqli_stmt_execute($insert); mysqli_stmt_close($insert); }
    }
    mysqli_stmt_close($check);
}

// Demo Student account for role-based access testing.
$studentEmail = 'student@studenthub.edu';
$studentHash = password_hash('Student@123', PASSWORD_DEFAULT);
$checkStudent = mysqli_prepare($conn, 'SELECT student_id FROM students WHERE email = ? LIMIT 1');
if ($checkStudent) {
    mysqli_stmt_bind_param($checkStudent, 's', $studentEmail);
    mysqli_stmt_execute($checkStudent);
    $studentResult = mysqli_stmt_get_result($checkStudent);
    if (!$studentResult || !$studentResult->fetch_assoc()) {
        $insertStudent = mysqli_prepare($conn, "INSERT INTO students (enrollment_no, full_name, email, username, password_hash, department, role, status) VALUES ('STU-001','Demo Student',?,'student',?,'Computer Science','Student','active')");
        if ($insertStudent) { mysqli_stmt_bind_param($insertStudent, 'ss', $studentEmail, $studentHash); mysqli_stmt_execute($insertStudent); mysqli_stmt_close($insertStudent); }
    }
    mysqli_stmt_close($checkStudent);
}
// Keep the demonstration student aligned with the submitted practical identity.
@mysqli_query($conn, "UPDATE students SET full_name='Niraj Visavadiya', department='Computer Science', role='Student', status='active' WHERE email='student@studenthub.edu'");
