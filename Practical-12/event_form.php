<?php
session_start();
require __DIR__ . '/db.php';
if (!isset($_SESSION['admin_id'], $_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Admin') { header('Location: login.php?message=unauthorized'); exit; }
$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;
$event = ['title'=>'','description'=>'','event_date'=>'','poster'=>null];
$errors = [];
if ($editing) {
    $statement = mysqli_prepare($conn, 'SELECT event_id, title, description, event_date, poster FROM events WHERE event_id = ?');
    mysqli_stmt_bind_param($statement, 'i', $id); mysqli_stmt_execute($statement); $event = mysqli_stmt_get_result($statement)->fetch_assoc(); mysqli_stmt_close($statement);
    if (!$event) { header('Location: admin_dashboard.php?message=error'); exit; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $eventDate = (string)($_POST['event_date'] ?? '');
    if ($title === '' || strlen($title) > 150) $errors[] = 'Enter an event title up to 150 characters.';
    if ($description === '') $errors[] = 'Enter an event description.';
    $date = DateTime::createFromFormat('Y-m-d', $eventDate);
    if (!$date || $date->format('Y-m-d') !== $eventDate) $errors[] = 'Choose a valid event date.';
    $posterName = $event['poster'];
    if (!$editing && (!isset($_FILES['poster']) || $_FILES['poster']['error'] === UPLOAD_ERR_NO_FILE)) {
        $errors[] = 'Please upload a poster before creating the event.';
    }
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if ($file['error'] !== UPLOAD_ERR_OK) $errors[] = 'Poster upload failed.';
        elseif ($file['size'] > 2 * 1024 * 1024) $errors[] = 'Poster must be 2 MB or smaller.';
        else { $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']); if (!isset($allowed[$mime])) $errors[] = 'Invalid file type. Upload JPG, PNG, or WebP only.'; else { $posterName = bin2hex(random_bytes(12)) . '.' . $allowed[$mime]; $posterPath = __DIR__ . '/uploads/posters/' . $posterName; if (!move_uploaded_file($file['tmp_name'], $posterPath)) $errors[] = 'Poster could not be saved.'; } }
    }
    if (!$errors) {
        if ($editing) { $statement = mysqli_prepare($conn, 'UPDATE events SET title=?, description=?, event_date=?, poster=? WHERE event_id=?'); mysqli_stmt_bind_param($statement, 'ssssi', $title, $description, $eventDate, $posterName, $id); $ok = mysqli_stmt_execute($statement); mysqli_stmt_close($statement); $message = 'updated'; }
        else { $statement = mysqli_prepare($conn, 'INSERT INTO events (title, description, event_date, poster) VALUES (?, ?, ?, ?)'); mysqli_stmt_bind_param($statement, 'ssss', $title, $description, $eventDate, $posterName); $ok = mysqli_stmt_execute($statement); mysqli_stmt_close($statement); $message = 'created'; }
        if ($ok) { header('Location: admin_dashboard.php?message=' . $message); exit; }
        $errors[] = 'Event could not be saved.';
    }
    $event = array_merge($event, ['title'=>$title,'description'=>$description,'event_date'=>$eventDate,'poster'=>$posterName]);
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $editing ? 'Edit' : 'Create' ?> Event | StudentHub</title><link rel="stylesheet" href="event_admin.css"></head><body><main class="page"><section class="panel form-panel"><p class="eyebrow">ADMIN DASHBOARD</p><h1><?= $editing ? 'Update event' : 'Create event' ?></h1><?php foreach($errors as $error): ?><p class="notice error" role="alert"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></p><?php endforeach; ?><form class="event-form" method="post" enctype="multipart/form-data" onsubmit="return validatePoster();"><label for="title">Event title</label><input id="title" name="title" required maxlength="150" value="<?=htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8')?>"><label for="description">Description</label><textarea id="description" name="description" required><?=htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')?></textarea><label for="event_date">Event date</label><input id="event_date" name="event_date" type="date" required value="<?=htmlspecialchars($event['event_date'], ENT_QUOTES, 'UTF-8')?>"><label for="poster">Poster<?= $editing ? '' : ' (required)' ?></label><input id="poster" name="poster" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>><span class="file-hint">Allowed: JPG, PNG, WebP. Maximum size: 2 MB.</span><?php if($event['poster']): ?><p class="file-hint">Current poster: <?=htmlspecialchars($event['poster'], ENT_QUOTES, 'UTF-8')?></p><?php endif; ?><div class="event-actions"><button class="button primary" type="submit"><?= $editing ? 'Update event' : 'Create event' ?></button><a class="button secondary" href="admin_dashboard.php">Cancel</a></div></form></section></main><script>function validatePoster(){const input=document.getElementById('poster');if(!input.files.length && <?= $editing ? 'false' : 'true' ?>){alert('Please upload a poster before creating the event.');input.focus();return false;}return true;}</script></body></html>
