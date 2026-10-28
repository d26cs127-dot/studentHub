<?php
session_start();
require __DIR__ . '/db.php';
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Admin') { header('Location: login.php?message=unauthorized'); exit; }
$id = (int)($_GET['id'] ?? 0);
$statement = mysqli_prepare($conn, 'SELECT poster FROM events WHERE event_id = ?');
mysqli_stmt_bind_param($statement, 'i', $id); mysqli_stmt_execute($statement); $event = mysqli_stmt_get_result($statement)->fetch_assoc(); mysqli_stmt_close($statement);
if ($event) {
    $statement = mysqli_prepare($conn, 'DELETE FROM events WHERE event_id = ?'); mysqli_stmt_bind_param($statement, 'i', $id); $ok = mysqli_stmt_execute($statement); mysqli_stmt_close($statement);
    if ($ok && $event['poster']) { $path = __DIR__ . '/uploads/posters/' . basename($event['poster']); if (is_file($path)) unlink($path); }
    header('Location: admin_dashboard.php?message=' . ($ok ? 'deleted' : 'error')); exit;
}
header('Location: admin_dashboard.php?message=error'); exit;
