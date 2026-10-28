<?php
session_start();
$timeoutSeconds = 300;
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: login.php?message=unauthorized');
    exit;
}
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeoutSeconds) {
    header('Location: timeout.php');
    exit;
}
$_SESSION['last_activity'] = time();
$destination = $_SESSION['role'] === 'Admin' ? 'admin_dashboard.php' : 'student_dashboard.php';
header('Location: ' . $destination);
exit;
