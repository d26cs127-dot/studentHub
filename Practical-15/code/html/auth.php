<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function require_login(): void {
  $timeout=1800;
  if (!isset($_SESSION['student_id']) || (isset($_SESSION['last_activity']) && time()-(int)$_SESSION['last_activity']>$timeout)) {
    $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>$p['httponly'],'samesite'=>$p['samesite']??'Lax']); }
    session_destroy(); header('Location: login.html?error=timeout'); exit;
  }
  $_SESSION['last_activity']=time();
}
function require_admin(): void { require_login(); if (($_SESSION['role']??'Student')!=='Admin') { http_response_code(403); header('Location: login.html?error=unauthorized'); exit; } }
function log_action(mysqli $conn,string $action,string $details=''): void { $q=mysqli_prepare($conn,'INSERT INTO audit_logs (student_id,action,details) VALUES (?,?,?)'); if($q){$id=(int)($_SESSION['student_id']??0);mysqli_stmt_bind_param($q,'iss',$id,$action,$details);mysqli_stmt_execute($q);mysqli_stmt_close($q);} }
function csrf_token(): string { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function require_csrf(?string $token): void { if (!$token || !hash_equals((string)($_SESSION['csrf_token']??''),$token)) { http_response_code(419); exit('Invalid CSRF token.'); } }
function restore_remembered_session(): void { if (isset($_SESSION['student_id']) || empty($_COOKIE['studenthub_remember'])) return; require_once __DIR__.'/db.php'; $hash=hash('sha256',(string)$_COOKIE['studenthub_remember']); $q=mysqli_prepare($conn,'SELECT s.student_id,s.full_name,s.role FROM remember_tokens t JOIN students s ON s.student_id=t.student_id WHERE t.token_hash=? AND t.expires_at>NOW() AND LOWER(s.status)=\'active\' LIMIT 1'); if($q){mysqli_stmt_bind_param($q,'s',$hash);mysqli_stmt_execute($q);$user=mysqli_stmt_get_result($q)->fetch_assoc();mysqli_stmt_close($q);if($user){session_regenerate_id(true);$_SESSION['student_id']=(int)$user['student_id'];$_SESSION['full_name']=$user['full_name'];$_SESSION['role']=$user['role'];$_SESSION['last_activity']=time();}} }
restore_remembered_session();

