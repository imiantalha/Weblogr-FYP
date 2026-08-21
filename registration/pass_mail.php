<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

if (!isset($email, $otp)) {
    throw new RuntimeException('Password reset email requires a recipient and OTP.');
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('MAIL_USERNAME') ?: '';
    $mail->Password = getenv('MAIL_PASSWORD') ?: '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: $mail->Username;
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Weblogr';

    if ($mail->Username === '' || $mail->Password === '' || $fromAddress === '') {
        throw new RuntimeException('Mail credentials are not configured.');
    }

    $mail->setFrom($fromAddress, $fromName);
    $mail->addAddress($email);
    $mail->Subject = 'Weblogr - OTP Verification';
    $mail->Body = 'Your OTP for Weblogr password reset is: ' . htmlspecialchars((string) $otp, ENT_QUOTES, 'UTF-8');
    $mail->send();

    header('Location: otp_verification.php?email=' . rawurlencode((string) $email) . '&reset=1');
    exit;
} catch (Exception $exception) {
    error_log('Password reset email failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Unable to send the password reset email. Please try again later.';
}
