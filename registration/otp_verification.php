<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

$purpose = $_SESSION['otp_purpose'] ?? null;
$email = $_SESSION['otp_email'] ?? null;
$expires_at = (int) ($_SESSION['otp_expires_at'] ?? 0);
$error_message = null;

if (!in_array($purpose, ['registration', 'password_reset'], true) || !is_string($email) || $expires_at < time()) {
    $error_message = 'This verification code has expired. Please request a new code.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $digits = [];
    for ($index = 1; $index <= 6; $index++) {
        $digit = (string) ($_POST['otp' . $index] ?? '');
        if (!preg_match('/^\d$/', $digit)) {
            $error_message = 'Please enter all six OTP digits.';
            break;
        }
        $digits[] = $digit;
    }

    if ($error_message === null) {
        $entered_otp = implode('', $digits);
        require '../database/db.php';

        $verified_condition = $purpose === 'password_reset' ? 1 : 0;
        $statement = $con->prepare('SELECT user_id, otp FROM users WHERE email = ? AND is_verified = ? LIMIT 1');
        $statement->bind_param('si', $email, $verified_condition);
        $statement->execute();
        $user = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($user === null || !hash_equals((string) $user['otp'], $entered_otp)) {
            $error_message = 'Invalid OTP. Please try again.';
        } else {
            $user_id = (int) $user['user_id'];
            $statement = $con->prepare("UPDATE users SET otp = '' WHERE user_id = ?");
            $statement->bind_param('i', $user_id);
            $statement->execute();
            $statement->close();

            if ($purpose === 'password_reset') {
                $_SESSION['password_reset_user_id'] = $user_id;
                unset($_SESSION['otp_purpose'], $_SESSION['otp_email'], $_SESSION['otp_expires_at']);
                $con->close();
                header('Location: reset_password.php');
                exit;
            }

            $statement = $con->prepare('UPDATE users SET is_verified = 1 WHERE user_id = ?');
            $statement->bind_param('i', $user_id);
            $statement->execute();
            $statement->close();
            $con->close();

            unset($_SESSION['otp_purpose'], $_SESSION['otp_email'], $_SESSION['otp_expires_at']);
            header('Location: success.php');
            exit;
        }

        $con->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Weblogr</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="otp-container">
        <h1>OTP Verification</h1>
        <h6>Please check your email, we've sent an OTP</h6>

        <?php if ($error_message !== null): ?>
            <p role="alert"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="input-container">
                <?php for ($index = 1; $index <= 6; $index++): ?>
                    <input type="text" inputmode="numeric" pattern="[0-9]" name="otp<?php echo $index; ?>" class="otp-digit" maxlength="1" required autocomplete="one-time-code">
                <?php endfor; ?>
            </div>
            <button type="submit" class="otp-button">Verify</button>
        </form>

        <a href="signup.php" class="resend-otp">Back to signup</a>
    </div>
</body>
</html>
