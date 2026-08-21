<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

if (isset($_SESSION['username'])) {
    header('Location: profile.php');
    exit;
}

$registration_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../database/db.php';

    $fullname = trim((string) ($_POST['fullname'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm_password = (string) ($_POST['confirm_password'] ?? '');

    if ($fullname === '' || mb_strlen($fullname) > 25) {
        $registration_error = 'Please enter a valid full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,255}$/', $username)) {
        $registration_error = 'Username must be 3-255 characters and contain only letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 8) {
        $registration_error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $registration_error = 'Passwords do not match.';
    } else {
        $statement = $con->prepare('SELECT username, email, is_verified FROM users WHERE username = ? OR email = ? LIMIT 1');
        $statement->bind_param('ss', $username, $email);
        $statement->execute();
        $existing_user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($existing_user !== null) {
            if ((int) $existing_user['is_verified'] === 1) {
                $registration_error = $existing_user['username'] === $username
                    ? 'Username already exists.'
                    : 'Email already exists.';
            } else {
                $registration_error = 'An unverified account already exists for this username or email. Complete verification before registering again.';
            }
        } else {
            $otp = random_int(100000, 999999);
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $statement = $con->prepare(
                'INSERT INTO users (fullname, username, email, password, otp, date, is_verified)
                 VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP(), 0)'
            );
            $statement->bind_param('sssss', $fullname, $username, $email, $password_hash, $otp);
            $statement->execute();
            $statement->close();
            $con->close();

            $_SESSION['otp_purpose'] = 'registration';
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expires_at'] = time() + 600;

            require 'mail.php';
            exit;
        }
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIGNUP</title>
  <script src="index.js"></script>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>

<body>
  <div class="center-items">
    <div class="container">
      <div class="welcome">
        <h1>Weblogr</h1>
      </div>
      <div class="wrapper">
        <div class="title"><span>Registration Form</span></div>

        <?php if ($registration_error !== null): ?>
          <p role="alert"><?php echo htmlspecialchars($registration_error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form onsubmit="return form_validation()" method="post">
          <div class="row">
            <i class="fas fa-user"></i>
            <input type="text" placeholder="Full Name" required name="fullname" id="fullname" maxlength="25" autocomplete="name">
          </div>
          <div class="row">
            <i class="fas fa-envelope"></i>
            <input type="email" placeholder="Email" required name="email" id="email" autocomplete="email">
          </div>
          <div class="row">
            <i class="fas fa-user"></i>
            <input type="text" placeholder="Username" required name="username" id="username" minlength="3" maxlength="255" autocomplete="username">
          </div>
          <div class="row">
            <i class="fas fa-lock"></i>
            <input type="password" placeholder="Password" required name="password" id="password" minlength="8" autocomplete="new-password">
          </div>
          <div class="row">
            <i class="fas fa-key"></i>
            <input type="password" placeholder="Confirm password" required name="confirm_password" id="confirm_password" minlength="8" autocomplete="new-password">
          </div>

          <div class="row button">
            <input type="submit" value="Signup" name="signup">
          </div>
          <div class="signup-link">Have an account? <a href="login.php">Login now</a></div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
