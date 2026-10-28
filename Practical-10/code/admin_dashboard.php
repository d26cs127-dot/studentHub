<?php
session_start();
require __DIR__ . '/db.php';
$timeoutSeconds = 300;
if (!isset($_SESSION['user_id'], $_SESSION['role'])) { header('Location: login.php?message=unauthorized'); exit; }
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeoutSeconds) { header('Location: timeout.php'); exit; }
if ($_SESSION['role'] !== 'Admin') { header('Location: login.php?message=unauthorized'); exit; }
$_SESSION['last_activity'] = time();
$totalUsers = (int)$mysqli->query('SELECT COUNT(*) AS total FROM login_users')->fetch_assoc()['total'];
$totalStudents = (int)$mysqli->query("SELECT COUNT(*) AS total FROM login_users WHERE role = 'Student'")->fetch_assoc()['total'];
$users = $mysqli->query('SELECT full_name, email, role, created_at FROM login_users ORDER BY role, full_name')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard | StudentHub</title><link rel="stylesheet" href="style.css"></head>
<body><main class="dashboard-shell"><section class="dashboard-card dashboard-admin"><p class="eyebrow">ADMIN CONTROL CENTER</p><h1>Welcome, <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></h1><p>You are signed in as <strong>Admin</strong>. This dashboard is restricted to the single administrator account.</p><div class="dashboard-grid"><div><strong><?= $totalUsers ?></strong><span>Total registered users</span></div><div><strong><?= $totalStudents ?></strong><span>Student accounts</span></div><div><strong>Admin tools</strong><span>Review users and roles.</span></div></div><h2>User and Role Management</h2><div class="user-table-wrap"><table class="user-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="role-badge <?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div><nav class="actions"><a href="student_dashboard.php">Try student dashboard</a><a href="logout.php" class="button">Log out</a></nav></section></main></body></html>
