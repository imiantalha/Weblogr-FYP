<?php

declare(strict_types=1);
require '../includes/security.php';
start_secure_session();
require '../database/db.php';
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    enforce_rate_limit('password-recovery', $email, 3, 900);
    $message = 'If the email is registered, a reset code will be sent.';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statement = $con->prepare('SELECT user_id, is_verified FROM users WHERE email = ? LIMIT 1');
        $statement->bind_param('s', $email); $statement->execute(); $user = $statement->get_result()->fetch_assoc(); $statement->close();
        if ($user !== null && (int) $user['is_verified'] === 1) {
            $otp = random_int(100000, 999999); $user_id = (int) $user['user_id'];
            $statement = $con->prepare('UPDATE users SET otp = ? WHERE user_id = ?'); $statement->bind_param('si', $otp, $user_id); $statement->execute(); $statement->close();
            $_SESSION['otp_purpose'] = 'password_reset'; $_SESSION['otp_email'] = $email; $_SESSION['otp_expires_at'] = time() + 600;
            $con->close(); require 'pass_mail.php'; exit;
        }
    }
}
$con->close();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password | Weblogr</title><script src="../assets/form-validation.js" defer></script><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><div class="container"><div class="wrapper"><div class="title">Reset your password</div><?php if($message!==null):?><p class="form-success" role="status"><?php echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');?></p><?php endif;?><form method="post" novalidate><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');?>"><div class="row"><i class="fas fa-envelope"></i><input type="email" placeholder="Email address" required name="email" autocomplete="email" data-label="Email"></div><div class="row button"><input type="submit" value="Send reset code"></div><div class="signup-link">Remembered it? <a href="login.php">Login</a></div></form></div></div></body></html>
