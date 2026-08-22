<?php
declare(strict_types=1);
require '../includes/security.php';
start_secure_session();

$purpose = $_SESSION['otp_purpose'] ?? null;
$email = $_SESSION['otp_email'] ?? null;
$expires_at = (int) ($_SESSION['otp_expires_at'] ?? 0);
$available_at = (int) ($_SESSION['otp_resend_available_at'] ?? 0);

if (!in_array($purpose, ['registration', 'password_reset'], true) || !is_string($email)) {
    header('Location: login.php');
    exit;
}

if ($available_at > time()) {
    $_SESSION['otp_error'] = 'Please wait before requesting another code.';
    header('Location: otp_verification.php');
    exit;
}

require '../database/db.php';
$verified_condition = $purpose === 'password_reset' ? 1 : 0;
$statement = $con->prepare('SELECT user_id FROM users WHERE email = ? AND is_verified = ? LIMIT 1');
$statement->bind_param('si', $email, $verified_condition);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();
$statement->close();

if ($user === null) {
    $con->close();
    $_SESSION['otp_error'] = 'We could not find the verification request. Please start again.';
    header('Location: login.php');
    exit;
}

$otp = random_int(100000, 999999);
$user_id = (int) $user['user_id'];
$statement = $con->prepare('UPDATE users SET otp = ? WHERE user_id = ?');
$statement->bind_param('si', $otp, $user_id);
$statement->execute();
$statement->close();
$con->close();

$_SESSION['otp_expires_at'] = time() + 600;
$_SESSION['otp_resend_available_at'] = time() + 30;

if ($purpose === 'password_reset') {
    require 'pass_mail.php';
} else {
    require 'mail.php';
}
