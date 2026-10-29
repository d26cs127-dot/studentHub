<?php
require __DIR__.'/../auth.php';
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['loggedIn'=>isset($_SESSION['student_id']),'role'=>$_SESSION['role']??null,'name'=>$_SESSION['full_name']??null]);
