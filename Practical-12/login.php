<?php
session_start();
require __DIR__ . '/db.php';
$message = $_GET['message'] ?? '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $statement = mysqli_prepare($conn, 'SELECT id, name, email, password_hash, role FROM event_admins WHERE email = ? LIMIT 1');
    mysqli_stmt_bind_param($statement, 's', $email);
    mysqli_stmt_execute($statement);
    $admin = mysqli_stmt_get_result($statement)->fetch_assoc();
    mysqli_stmt_close($statement);
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['last_activity'] = time();
        header('Location: admin_dashboard.php?message=login_success');
        exit;
    }
    $error = 'Invalid administrator email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login | StudentHub</title><link rel="stylesheet" href="event_admin.css"></head><body><main class="auth-page"><section class="auth-card"><p class="eyebrow">STUDENTHUB ADMINISTRATION</p><h1>Admin login</h1><p class="muted">Sign in to manage events and poster uploads.</p><?php if($message==='logged_out'): ?><p class="notice success">You have been logged out successfully.</p><?php elseif($message==='unauthorized'): ?><p class="notice error">Unauthorized access. Administrator permission is required.</p><?php endif; ?><?php if($error): ?><p class="notice error" role="alert"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p><?php endif; ?><form method="post" class="auth-form"><label for="email">Admin email</label><input id="email" name="email" type="email" required autocomplete="username"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"><button class="button primary" type="submit">Sign in</button></form></section></main></body></html>
