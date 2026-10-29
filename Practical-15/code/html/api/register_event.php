<?php
require __DIR__.'/../auth.php'; require_login(); require __DIR__.'/../db.php';
header('Content-Type: application/json; charset=utf-8');
$body=json_decode(file_get_contents('php://input'),true)??[];$event=(int)($body['event_id']??0);$student=(int)$_SESSION['student_id'];
if(!$event){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Event id is required.']);exit;}
$q=mysqli_prepare($conn,'SELECT registration_id FROM registrations WHERE student_id=? AND event_id=? LIMIT 1');mysqli_stmt_bind_param($q,'ii',$student,$event);mysqli_stmt_execute($q);$exists=mysqli_stmt_get_result($q)->fetch_assoc();mysqli_stmt_close($q);if($exists){echo json_encode(['success'=>false,'message'=>'You are already registered.']);exit;}
$q=mysqli_prepare($conn,'INSERT INTO registrations(student_id,event_id) VALUES(?,?)');mysqli_stmt_bind_param($q,'ii',$student,$event);$ok=mysqli_stmt_execute($q);mysqli_stmt_close($q);echo json_encode(['success'=>$ok,'message'=>$ok?'Registration successful.':'Registration could not be saved.']);
