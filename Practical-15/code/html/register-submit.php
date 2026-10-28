<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
$errors=[]; $success=false; $submittedName='';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $errors['form']='Please submit the registration form using POST.'; http_response_code(405); }
else {
 $name=trim((string)($_POST['full-name']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $username=strtolower(trim((string)($_POST['username']??''))); $mobile=preg_replace('/[\s()\-]/','',(string)($_POST['mobile']??'')); $course=(string)($_POST['course']??''); $year=(string)($_POST['year']??''); $gender=(string)($_POST['gender']??''); $password=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm-password']??''); $submittedName=$name;
 if (!preg_match("/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u",$name)) $errors['full-name']='Enter a valid name.';
 if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors['email']='Enter a valid email address.';
 if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/',$username)) $errors['username']='Use 3–30 letters, numbers, or underscores.';
 if (!preg_match('/^(?:\+?91)?[6-9][0-9]{9}$/',$mobile)) $errors['mobile']='Enter a valid mobile number.';
 if (!in_array($course,['btech-cs','btech-it','btech-ec','btech-me','mtech-cs','mca','msc-it','bca','other'],true)) $errors['course']='Choose a valid course.';
 if (!in_array($year,['1','2','3','4','5'],true)) $errors['year']='Choose your year.';
 if (!in_array($gender,['female','male','nonbinary','prefer-not-to-say'],true)) $errors['gender']='Choose a gender option.';
 if (!preg_match('/^(?=.{8,64}$)(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S+$/',$password)) $errors['password']='Use 8–64 characters with a letter, number, and symbol.';
 if ($password!==$confirm) $errors['confirm-password']='The passwords do not match.';
 if (!isset($_POST['terms'])) $errors['terms']='Accept the terms to continue.';
 if (!$errors) {
  $q=mysqli_prepare($conn,'SELECT student_id FROM students WHERE email=? OR username=? LIMIT 1'); mysqli_stmt_bind_param($q,'ss',$email,$username); mysqli_stmt_execute($q); $dup=mysqli_stmt_get_result($q)->fetch_assoc(); mysqli_stmt_close($q);
  if ($dup) { $errors['form']='This email or username is already registered.'; http_response_code(409); }
  else { $hash=password_hash($password,PASSWORD_DEFAULT); $enrol='REG-'.strtoupper(substr(hash('sha256',$username.microtime(true)),0,12)); $q=mysqli_prepare($conn,"INSERT INTO students (enrollment_no,full_name,email,username,password_hash,department,role,status) VALUES (?,?,?,?,?,?, 'Student','active')"); mysqli_stmt_bind_param($q,'ssssss',$enrol,$name,$email,$username,$hash,$course); $success=mysqli_stmt_execute($q); mysqli_stmt_close($q); if(!$success){$errors['form']='Could not save your registration.';http_response_code(500);} }
 }
 if ($errors && http_response_code()===200) http_response_code(422);
}
function h(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $success?'Registration received':'Check your registration' ?> | StudentHub</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="css/style.css"></head><body><main class="max-w-[760px] mx-auto px-6 py-12"><?php if($success): ?><section class="server-feedback server-feedback-success" role="status"><h1>Registration complete</h1><p>Thank you, <?=h($submittedName)?>. Your account was saved successfully.</p><p class="text-sm">Your password is stored as a secure hash.</p></section><?php else: ?><section class="server-feedback server-feedback-error" role="alert"><h1>Registration not complete</h1><ul class="server-error-list"><?php foreach($errors as $e): ?><li><?=h($e)?></li><?php endforeach;?></ul></section><?php endif;?><div class="server-feedback-actions"><a href="register.html">Return to registration</a><a href="index.html">StudentHub home</a></div></main></body></html>
