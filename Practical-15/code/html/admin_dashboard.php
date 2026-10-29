<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
require_admin();
require __DIR__ . '/db.php';
$stats = [];
foreach (['students' => 'Students', 'events' => 'Events', 'registrations' => 'Registrations'] as $table => $label) {
    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM {$table}");
    $stats[$label] = (int)($result ? ($result->fetch_assoc()['total'] ?? 0) : 0);
}
$logs = mysqli_query($conn, 'SELECT action, details, created_at FROM audit_logs ORDER BY log_id DESC LIMIT 8');
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard | StudentHub</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="css/style.css?v=20261028"></head>
<body class="admin-dashboard-page"><main class="admin-shell max-w-[1200px] mx-auto px-6 py-10">
<header class="admin-topbar"><div><p class="eyebrow">ADMIN DASHBOARD</p><h1>StudentHub administration</h1><p>Welcome, <?= htmlspecialchars((string)$_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>. Manage events and review portal activity.</p></div><a class="admin-logout" href="logout.php">Logout</a></header>
<section class="admin-stat-grid" aria-label="Portal statistics"><?php foreach ($stats as $label => $value): ?><article><span><?= $value ?></span><p><?= $label ?></p></article><?php endforeach; ?></section>
<section class="admin-panel" aria-labelledby="events-title"><div class="admin-section-title"><div><p class="eyebrow">MANAGE CONTENT</p><h2 id="events-title">Events</h2><p>Create events with a validated poster. The event list refreshes without a page reload.</p></div></div>
<form id="event-form" enctype="multipart/form-data" class="admin-event-form"><label>Event name<input name="event_name" required placeholder="Enter event name"></label><label>Event date<input name="event_date" type="date" required></label><label>Description<input name="description" placeholder="Brief description"></label><label>Poster<input name="poster" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required></label><button type="submit">Add event</button></form><p id="event-message" role="status" aria-live="polite"></p><div id="event-list" class="admin-event-list" aria-live="polite"></div></section>
<section class="admin-panel" aria-labelledby="audit-title"><div class="admin-section-title"><div><p class="eyebrow">ACTIVITY</p><h2 id="audit-title">Recent audit log</h2></div></div><div class="admin-table-wrap"><table><thead><tr><th>Action</th><th>Details</th><th>Time</th></tr></thead><tbody><?php while ($log = $logs ? $logs->fetch_assoc() : null): ?><tr><td><?= htmlspecialchars((string)$log['action'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)($log['details'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string)$log['created_at'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endwhile; ?></tbody></table></div></section>
</main><script>
const list=document.getElementById('event-list'),form=document.getElementById('event-form'),message=document.getElementById('event-message');
function showMessage(text,good=true){message.textContent=text;message.dataset.state=good?'success':'error';}
async function loadEvents(){try{const response=await fetch('api_events.php'),json=await response.json();list.replaceChildren();(json.data||[]).forEach(event=>{const card=document.createElement('article');card.innerHTML='<h3></h3><p></p><button type="button">Delete</button>';card.querySelector('h3').textContent=event.event_name;if(event.poster){const image=document.createElement('img');image.src='uploads/posters/'+encodeURIComponent(event.poster);image.alt='Poster for '+event.event_name;image.className='admin-event-poster';image.loading='lazy';card.insertBefore(image,card.querySelector('h3'));}card.querySelector('p').textContent=event.event_date+' — '+(event.description||'No description')+(event.poster?' · Poster uploaded':'');card.querySelector('button').addEventListener('click',async()=>{if(!confirm('Delete this event?'))return;const response=await fetch('api_events.php',{method:'DELETE',headers:{'Content-Type':'application/json'},body:JSON.stringify({event_id:event.event_id})});const json=await response.json();showMessage(json.message,Boolean(json.success));if(json.success)loadEvents();});list.append(card);});}catch(error){showMessage('Could not load events.',false);}}
form.addEventListener('submit',async(event)=>{event.preventDefault();const response=await fetch('api_events.php',{method:'POST',body:new FormData(form)});const json=await response.json();showMessage(json.message,Boolean(json.success));if(json.success){form.reset();loadEvents();}});loadEvents();
</script></body></html>
