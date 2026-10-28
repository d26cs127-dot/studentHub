<?php
$errors = [];
$submittedName = '';
$success = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    $errors['form'] = 'Please submit the registration form.';
} else {
    $name = trim((string)($_POST['full-name'] ?? ''));
    $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $username = strtolower(trim((string)($_POST['username'] ?? '')));
    $mobile = preg_replace('/[\s()\-]/', '', trim((string)($_POST['mobile'] ?? ''))) ?? '';
    $course = (string)($_POST['course'] ?? '');
    $year = (string)($_POST['year'] ?? '');
    $gender = (string)($_POST['gender'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm-password'] ?? '');
    $termsAccepted = isset($_POST['terms']);
    $submittedName = $name;

    if ($name === '') {
        $errors['full-name'] = 'Name is required.';
    } elseif (!preg_match("/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u", $name) || preg_match_all('/./us', $name) > 50) {
        $errors['full-name'] = 'Enter a valid name (up to 50 characters).';
    }
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($username === '') {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Use 3–30 letters, numbers, or underscores for the username.';
    }
    if ($mobile === '' || !preg_match('/^(?:\+?91)?[6-9][0-9]{9}$/', $mobile)) {
        $errors['mobile'] = 'Enter a valid 10-digit mobile number, optionally with the +91 country code.';
    }
    $allowedCourses = ['btech-cs', 'btech-it', 'btech-ec', 'btech-me', 'mtech-cs', 'mca', 'msc-it', 'bca', 'other'];
    if (!in_array($course, $allowedCourses, true)) {
        $errors['course'] = 'Choose a course from the list.';
    }
    if (!in_array($year, ['1', '2', '3', '4', '5'], true)) {
        $errors['year'] = 'Choose your year of study.';
    }
    if (!in_array($gender, ['female', 'male', 'nonbinary', 'prefer-not-to-say'], true)) {
        $errors['gender'] = 'Choose a gender option, or select “Prefer not to say”.';
    }
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (!preg_match('/^(?=.{8,64}$)(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S+$/', $password)) {
        $errors['password'] = 'Use 8–64 characters with a letter, a number, and a symbol. Spaces are not allowed.';
    }
    if ($confirmPassword === '') {
        $errors['confirm-password'] = 'Please confirm your password.';
    } elseif (!hash_equals($password, $confirmPassword)) {
        $errors['confirm-password'] = 'The passwords do not match.';
    }
    if (!$termsAccepted) {
        $errors['terms'] = 'Please accept the Terms of Service and Privacy Policy.';
    }

    if (!$errors) {
        try {
            require __DIR__ . '/db.php';
            $duplicateCheck = $pdo->prepare('SELECT email, username FROM students WHERE email = :email OR username = :username LIMIT 1');
            $duplicateCheck->execute(['email' => $email, 'username' => $username]);
            $duplicate = $duplicateCheck->fetch();

            if ($duplicate) {
                if (strcasecmp((string)$duplicate['email'], $email) === 0) {
                    $errors['email'] = 'This email is already registered.';
                }
                if ($duplicate['username'] !== null && strcasecmp((string)$duplicate['username'], $username) === 0) {
                    $errors['username'] = 'This username is already taken.';
                }
                http_response_code(409);
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $enrollmentNumber = 'REG-' . strtoupper(substr(hash('sha256', $username . microtime(true)), 0, 12));
                $insert = $pdo->prepare('INSERT INTO students (enrollment_no, full_name, email, username, password_hash, department) VALUES (:enrollment_no, :full_name, :email, :username, :password_hash, :department)');
                $insert->execute([
                    'enrollment_no' => $enrollmentNumber,
                    'full_name' => $name,
                    'email' => $email,
                    'username' => $username,
                    'password_hash' => $passwordHash,
                    'department' => $course,
                ]);
                $success = true;
            }
        } catch (PDOException $exception) {
            $mysqlCode = (int)($exception->errorInfo[1] ?? 0);
            if ($mysqlCode === 1062) {
                $constraint = (string)($exception->errorInfo[2] ?? '');
                if (str_contains($constraint, 'username')) {
                    $errors['username'] = 'This username is already taken.';
                } elseif (str_contains($constraint, 'email')) {
                    $errors['email'] = 'This email is already registered.';
                } else {
                    $errors['form'] = 'This email or username is already registered.';
                }
                http_response_code(409);
            } else {
                error_log('StudentHub registration database error: ' . $exception->getMessage());
                $errors['form'] = $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'We could not save your registration. Check the database setup and try again.';
                http_response_code(500);
            }
        } catch (RuntimeException $exception) {
            $errors['form'] = $exception->getMessage();
            http_response_code(500);
        }
    } else {
        http_response_code(422);
    }
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$courseNames = [
    'btech-cs' => 'B.Tech — Computer Science',
    'btech-it' => 'B.Tech — Information Technology',
    'btech-ec' => 'B.Tech — Electronics & Communication',
    'btech-me' => 'B.Tech — Mechanical Engineering',
    'mtech-cs' => 'M.Tech — Computer Science',
    'mca' => 'MCA',
    'msc-it' => 'M.Sc. IT',
    'bca' => 'BCA',
    'other' => 'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="StudentHub registration result." />
  <title><?= $success ? 'Registration received' : 'Check your registration' ?> — StudentHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to main content</a>
  <header class="bg-[#0f1729] shadow-lg" role="banner">
    <div class="max-w-[1200px] mx-auto px-6 py-4">
      <a href="index.html" class="flex items-center gap-2 no-underline" aria-label="StudentHub home">
        <span class="w-10 h-10 bg-[#fbbf24] rounded-xl inline-flex items-center justify-center text-xl font-extrabold text-[#0f1729]" aria-hidden="true">S</span>
        <span class="font-outfit text-2xl font-extrabold text-white">Student<span class="text-[#fbbf24]">Hub</span></span>
      </a>
    </div>
  </header>
  <main id="main-content" class="max-w-[760px] mx-auto px-6 py-12">
    <?php if ($success): ?>
      <section class="server-feedback server-feedback-success" role="status" aria-labelledby="result-title">
        <p class="eyebrow">REGISTRATION COMPLETE</p>
        <h1 id="result-title" class="text-2xl md:text-3xl font-bold">Thank you, <?= h($submittedName) ?>!</h1>
        <p>Your registration was submitted successfully and saved in the database.</p>
        <p class="text-sm">Your password is stored as a secure hash and is never saved as plain text.</p>
      </section>
    <?php else: ?>
      <section class="server-feedback server-feedback-error" role="alert" aria-labelledby="result-title">
        <p class="eyebrow">REGISTRATION NOT COMPLETE</p>
        <h1 id="result-title" class="text-2xl md:text-3xl font-bold">Please check your form</h1>
        <p>We couldn’t submit the registration yet:</p>
        <ul class="server-error-list">
          <?php foreach ($errors as $field => $message): ?>
            <li>
              <?php if (isset($courseNames[$field])): ?>
                <strong><?= h($courseNames[$field]) ?>:</strong>
              <?php elseif ($field === 'form'): ?>
                <strong>Submission:</strong>
              <?php else: ?>
                <strong><?= h(ucfirst(str_replace('-', ' ', $field))) ?>:</strong>
              <?php endif; ?>
              <?= h($message) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
    <div class="server-feedback-actions">
      <a class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm bg-[#fbbf24] text-[#0f1729] hover:bg-[#fcd34d] transition-all" href="register.html">Return to registration form</a>
      <a class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-sm border border-[#d1d9f0] text-[#1a1f36] hover:bg-[#edf2ff] transition-all" href="index.html">StudentHub home</a>
    </div>
  </main>
</body>
</html>
