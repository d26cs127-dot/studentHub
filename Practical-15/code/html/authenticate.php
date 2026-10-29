<?php
declare(strict_types=1);
require __DIR__.'/db.php'; require __DIR__.'/auth.php';
require_csrf($_POST['csrf_token'] ?? null);
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: login.html');exit;}
$identity=strtolower(trim((string)($_POST['email']??''))); $password=(string)($_POST['password']??'');
$q=mysqli_prepare($conn,'SELECT student_id,full_name,email,username,password_hash,role,status FROM students WHERE email=? OR username=? LIMIT 1');
mysqli_stmt_bind_param($q,'ss',$identity,$identity); mysqli_stmt_execute($q); $user=mysqli_stmt_get_result($q)->fetch_assoc(); mysqli_stmt_close($q);
if(!$user || strtolower((string)($user['status']??'active'))!=='active' || !$user['password_hash'] || !password_verify($password,$user['password_hash'])){header('Location: login.html?error=invalid');exit;}
session_regenerate_id(true); $_SESSION['student_id']=(int)$user['student_id']; $_SESSION['full_name']=$user['full_name']; $_SESSION['role']=$user['role']; $_SESSION['last_activity']=time(); $_SESSION['login_time']=time();
$last=mysqli_prepare($conn,'UPDATE students SET last_login=NOW() WHERE student_id=?');
if($last){mysqli_stmt_bind_param($last,'i',$_SESSION['student_id']);mysqli_stmt_execute($last);mysqli_stmt_close($last);}
if(isset($_POST['remember_me'])){$raw=bin2hex(random_bytes(32));$hash=hash('sha256',$raw);$expires=date('Y-m-d H:i:s',time()+2592000);$token=mysqli_prepare($conn,'INSERT INTO remember_tokens (student_id,token_hash,expires_at) VALUES (?,?,?)');if($token){mysqli_stmt_bind_param($token,'iss',$_SESSION['student_id'],$hash,$expires);mysqli_stmt_execute($token);mysqli_stmt_close($token);setcookie('studenthub_remember',$raw,['expires'=>time()+2592000,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);}}
log_action($conn,'login','Successful login');
header('Location: '.($user['role']==='Admin'?'admin.php':'dashboard.php')); exit;

