<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();
require '../database/db.php';

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'If the email is registered, a reset code will be sent.';
    } else {
        $statement = $con->prepare('SELECT user_id, is_verified FROM users WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($user !== null && (int) $user['is_verified'] === 1) {
            $otp = random_int(100000, 999999);
            $statement = $con->prepare('UPDATE users SET otp = ? WHERE user_id = ?');
            $user_id = (int) $user['user_id'];
            $statement->bind_param('si', $otp, $user_id);
            $statement->execute();
            $statement->close();

            $_SESSION['otp_purpose'] = 'password_reset';
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_expires_at'] = time() + 600;

            $con->close();
            require 'pass_mail.php';
            exit;
        }

        $message = 'If the email is registered, a reset code will be sent.';
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>

<body>
    <div class="container">
        <div class="wrapper">
            <div class="title">Registered Email</div>
            <?php if ($message !== null): ?>
                <p role="status"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <form name="forgot-password" method="post">
                <div class="row">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email" required name="email" id="email" autocomplete="email">
                </div>
                <div class="row button">
                    <input type="submit" value="Reset" name="reset">
                </div>
                <div class="signup-link">Remembered it? <a href="login.php">Login</a></div>
            </form>
        </div>
    </div>
</body>
</html>
