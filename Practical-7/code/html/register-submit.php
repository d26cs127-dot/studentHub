<?php
$errors = [];
$submittedName = '';
$success = false;
$storageError = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    $errors['form'] = 'This page accepts registration submissions only. Please use the registration form.';
} else {
    $name = trim((string)($_POST['full-name'] ?? ''));
    $name = preg_replace('/\s+/', ' ', $name);
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $mobileInput = trim((string)($_POST['mobile'] ?? ''));
    $mobile = preg_replace('/[\s()\-]/', '', $mobileInput);
    $course = (string)($_POST['course'] ?? '');
    $year = (string)($_POST['year'] ?? '');
    $gender = (string)($_POST['gender'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm-password'] ?? '');
    $termsAccepted = isset($_POST['terms']);
    $submittedName = $name;

    if ($name === '' || !preg_match("/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u", $name) || strlen($name) > 50) {
        $errors['full-name'] = 'Enter a name using letters, spaces, apostrophes, or hyphens (up to 50 characters).';
    }
    if ($email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($mobile === '' || !preg_match('/^(?:\+?91)?[6-9][0-9]{9}$/', $mobile)) {
        $errors['mobile'] = 'Enter a valid 10-digit mobile number, optionally with the +91 country code.';
    }

    $allowedCourses = [
        'btech-cs', 'btech-it', 'btech-ec', 'btech-me',
        'mtech-cs', 'mca', 'msc-it', 'bca', 'other'
    ];
    if (!in_array($course, $allowedCourses, true)) {
        $errors['course'] = 'Choose a course from the list.';
    }
    if (!in_array($year, ['1', '2', '3', '4', '5'], true)) {
        $errors['year'] = 'Choose a valid year of study.';
    }
    if (!in_array($gender, ['female', 'male', 'nonbinary', 'prefer-not-to-say'], true)) {
        $errors['gender'] = 'Choose a gender option, or select “Prefer not to say”.';
    }
    if (!preg_match('/^(?=.{8,64}$)(?=.*[A-Za-z])(?=.*[0-9])(?=.*[^A-Za-z0-9\s])\S+$/', $password)) {
        $errors['password'] = 'Use 8–64 characters with a letter, a number, and a symbol. Spaces are not allowed.';
    }
    if ($confirmPassword === '' || $password !== $confirmPassword) {
        $errors['confirm-password'] = 'The passwords do not match.';
    }
    if (!$termsAccepted) {
        $errors['terms'] = 'Please accept the Terms of Service and Privacy Policy.';
    }

    if (!$errors) {
        $storageDirectory = __DIR__ . '/storage';
        if (!is_dir($storageDirectory)) {
            mkdir($storageDirectory, 0777, true);
        }

        if (!is_dir($storageDirectory)) {
            $storageError = true;
        } else {
            $csvPath = $storageDirectory . '/registrations.csv';
            $file = fopen($csvPath, 'a');

            if ($file === false) {
                $storageError = true;
            } else {
                if (!file_exists($csvPath) || filesize($csvPath) === 0) {
                    fputcsv($file, ['submitted_at', 'full_name', 'email', 'mobile', 'course', 'year', 'gender', 'password_hash']);
                }
                $row = [
                    date('Y-m-d H:i:s'),
                    $name,
                    $email,
                    $mobile,
                    $course,
                    $year,
                    $gender,
                    password_hash($password, PASSWORD_DEFAULT)
                ];
                $written = fputcsv($file, $row) !== false;
                fclose($file);

                if ($written) {
                    $success = true;
                } else {
                    $storageError = true;
                }
            }
        }

        if ($storageError) {
            $errors['form'] = 'Your registration could not be saved. Please try again later.';
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
        <p>Your registration was submitted successfully and saved.</p>
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