<?php
require __DIR__.'/auth.php';
$code=strtoupper(substr(bin2hex(random_bytes(4)),0,6));
$_SESSION['captcha_code']=$code;
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['code'=>$code]);
