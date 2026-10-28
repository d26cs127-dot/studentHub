<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_login();
require __DIR__ . '/db.php';
$name = (string)($_SESSION['full_name'] ?? 'Student');
$department = 'StudentHub';
$enrollment = '';
$q = mysqli_prepare($conn, 'SELECT full_name, department, enrollment_no FROM students WHERE student_id = ? LIMIT 1');
if ($q) {
    $id = (int)$_SESSION['student_id'];
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $record = mysqli_stmt_get_result($q)->fetch_assoc() ?: [];
    mysqli_stmt_close($q);
    $name = (string)($record['full_name'] ?? $name);
    $department = (string)($record['department'] ?? $department);
    $enrollment = (string)($record['enrollment_no'] ?? '');
}
$html = file_get_contents(__DIR__ . '/dashboard.html');
$html = str_replace('href="css/style.css"', 'href="css/style.css?v=20261028"', $html);
$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeDepartment = htmlspecialchars($department, ENT_QUOTES, 'UTF-8');
$safeEnrollment = htmlspecialchars($enrollment, ENT_QUOTES, 'UTF-8');
$html = str_replace('Good morning, Niraj 👋', 'Good morning, ' . $safeName . ' 👋', $html);
$html = preg_replace('/Here.s a snapshot of your academic life —.*?<\/p>/u', 'Here\'s a snapshot of your academic life — ' . $safeDepartment . ($safeEnrollment ? ' · Enrollment ' . $safeEnrollment : '') . '.</p>', $html, 1) ?? $html;
echo $html;
