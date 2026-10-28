<?php
session_start();
$timeoutSeconds = 300;
if (!isset($_SESSION['user_id'], $_SESSION['role'])) { header('Location: login.php?message=unauthorized'); exit; }
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeoutSeconds) { header('Location: timeout.php'); exit; }
if ($_SESSION['role'] !== 'Student') { header('Location: login.php?message=unauthorized'); exit; }
$_SESSION['last_activity'] = time();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Dashboard | StudentHub</title><link rel="stylesheet" href="style.css"></head>
<body><main class="dashboard-shell"><section class="dashboard-card dashboard-student"><p class="eyebrow">STUDENT LEARNING SPACE</p><h1>Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></h1><p>You are signed in as <strong>Student</strong>. Your academic tools are available here.</p><div class="dashboard-grid"><div><strong>Attendance</strong><span>Check your subject attendance.</span></div><div><strong>Assignments</strong><span>Review pending academic work.</span></div><div><strong>Study materials</strong><span>Open notes, PDFs, and presentations.</span></div><div><strong>Campus events</strong><span>See upcoming StudentHub events.</span></div></div><p class="hint">Student accounts cannot open administrator tools.</p><nav class="actions"><a href="admin_dashboard.php">Try admin dashboard</a><a href="logout.php" class="button">Log out</a></nav></section></main></body></html>
