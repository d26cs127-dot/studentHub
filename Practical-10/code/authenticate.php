<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$password = (string)($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: login.php?error=invalid');
    exit;
}

$statement = $mysqli->prepare('SELECT user_id, full_name, email, password_hash, role FROM login_users WHERE email = ? LIMIT 1');
$statement->bind_param('s', $email);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    header('Location: login.php?error=invalid');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['last_activity'] = time();
$_SESSION['login_session_id'] = session_id();

$destination = $user['role'] === 'Admin' ? 'admin_dashboard.php' : 'dashboard.php';
header('Location: ' . $destination);
exit;
