<?php
session_start();
$requestedMessage = $_GET['message'] ?? '';
$currentRole = $_SESSION['role'] ?? '';
$currentName = $_SESSION['full_name'] ?? '';
$messages = [
    'logged_out' => 'You have been logged out successfully.',
    'timeout' => 'Your session expired due to inactivity. Please log in again.',
    'unauthorized' => 'You are not authorized to access that dashboard.',
];
$message = $messages[$_GET['message'] ?? ''] ?? '';
$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login | StudentHub</title><link rel="stylesheet" href="style.css"></head>
<body>
  <main class="auth-shell">
    <section class="auth-card" aria-labelledby="login-title">
      <div class="brand"><span>S</span> Student<span>Hub</span></div>
      <h1 id="login-title">Sign in to StudentHub</h1>
      <p class="muted">Use your registered email and password.</p>
      <?php if ($currentRole): ?><p class="message success" role="status">Currently signed in as <?= htmlspecialchars($currentName, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($currentRole, ENT_QUOTES, 'UTF-8') ?>). Submitting another account will switch sessions.</p><?php endif; ?>
      <?php if ($message): ?><p class="message success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
      <?php if ($requestedMessage === 'unauthorized'): ?><p class="message error" role="alert">Unauthorized access. Your role cannot open that dashboard.</p><?php endif; ?>
      <?php if ($error === 'invalid'): ?><p class="message error" role="alert">Invalid email or password.</p><?php endif; ?>
      <form action="authenticate.php" method="post" class="auth-form">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">Log in</button>
      </form>
      <p class="hint">Demo accounts are listed in <code>user_registration.sql</code>.</p>
      <?php if ($currentRole): ?><p><a href="logout.php">Log out current account</a></p><?php endif; ?>
    </section>
  </main>
</body>
</html>
