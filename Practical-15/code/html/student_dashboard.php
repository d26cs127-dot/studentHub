<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_login();
require __DIR__ . '/db.php';
$studentName = (string)($_SESSION['full_name'] ?? 'Student');
$studentEmail = '';
$q = mysqli_prepare($conn, 'SELECT full_name, email, department, enrollment_no FROM students WHERE student_id = ? LIMIT 1');
if ($q) {
    $id = (int)$_SESSION['student_id'];
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $record = mysqli_stmt_get_result($q)->fetch_assoc() ?: [];
    mysqli_stmt_close($q);
    $studentName = (string)($record['full_name'] ?? $studentName);
    $studentEmail = (string)($record['email'] ?? '');
}
$safeName = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Dashboard | StudentHub</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="css/style.css?v=20261028"></head>
<body class="student-dashboard-page"><main class="student-shell max-w-[1200px] mx-auto px-6 py-10">
<header class="student-topbar"><div><p class="eyebrow">STUDENT DASHBOARD</p><h1>Good morning, <?= $safeName ?> 👋</h1><p class="student-subtitle">Your academic workspace is ready. Use the quick links below to continue.</p></div><a class="student-logout" href="logout.php">Logout</a></header>
<section class="student-summary"><div class="student-avatar" aria-hidden="true"><?= strtoupper(substr($studentName, 0, 1)) ?></div><div><h2>Welcome, <?= $safeName ?></h2><p><?= htmlspecialchars($studentEmail, ENT_QUOTES, 'UTF-8') ?></p><span class="student-badge">Student access</span></div></section>
<section class="student-actions" aria-label="Student dashboard links"><a href="dashboard.php"><span>📊</span><strong>Open academic dashboard</strong><small>Attendance, timetable, notices and student directory</small></a><a href="notice-board.html"><span>📢</span><strong>View notices and events</strong><small>Read the latest campus updates</small></a><a href="timetable.html"><span>🗓️</span><strong>Academic calendar</strong><small>Review your weekly class schedule</small></a><a href="profile.html"><span>👤</span><strong>View profile</strong><small>Review your StudentHub information</small></a></section>
</main></body></html>
