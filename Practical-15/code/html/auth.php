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

