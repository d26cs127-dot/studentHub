<?php
require __DIR__.'/auth.php';
require_login();
require __DIR__.'/db.php';
$id=(int)$_SESSION['student_id'];
$q=mysqli_prepare($conn,'SELECT full_name,email,department,enrollment_no FROM students WHERE student_id=?');
mysqli_stmt_bind_param($q,'i',$id);mysqli_stmt_execute($q);$student=mysqli_stmt_get_result($q)->fetch_assoc()?:[];mysqli_stmt_close($q);
function h(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Profile | StudentHub</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="css/style.css"></head><body><main class="max-w-[1200px] mx-auto px-6 py-10"><a class="skip-link" href="#main-content">Skip to main content</a><header class="student-topbar"><div><p class="eyebrow">PROFILE</p><h1 id="main-content"><?=h($student['full_name']??'Student')?></h1><p>Review your StudentHub account information.</p></div><a class="student-logout" href="logout.php">Logout</a></header><section class="server-feedback"><h2>Account details</h2><p><strong>Email:</strong> <?=h($student['email']??'')?></p><p><strong>Course:</strong> <?=h($student['department']??'')?></p><p><strong>Enrollment:</strong> <?=h($student['enrollment_no']??'')?></p></section><p><a href="dashboard.php">Back to dashboard</a></p></main><script src="js/script.js"></script></body></html>
