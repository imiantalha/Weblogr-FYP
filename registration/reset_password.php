<?php

declare(strict_types=1);

require '../includes/security.php';
start_secure_session();
$user_id = filter_var($_SESSION['password_reset_user_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$user_id) { header('Location: forgot_password.php'); exit; }
$error_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string) ($_POST['password'] ?? '');
    $confirm_password = (string) ($_POST['confirm_password'] ?? '');
    if (strlen($password) < 8) $error_message = 'Password must be at least 8 characters long.';
    elseif ($password !== $confirm_password) $error_message = 'Passwords do not match.';
    else {
        require '../database/db.php';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $statement = $con->prepare('UPDATE users SET password = ? WHERE user_id = ? AND is_verified = 1');
        $statement->bind_param('si', $hashed_password, $user_id);
        $statement->execute();
        $updated = $statement->affected_rows;
        $statement->close(); $con->close();
        if ($updated === 1) { unset($_SESSION['password_reset_user_id']); header('Location: login.php'); exit; }
        $error_message = 'Unable to reset the password. Please try again.';
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reset Password | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head><body><div class="container"><div class="welcome"><h1>Welcome back to Weblogr</h1></div><div class="wrapper"><div class="title">Reset Password</div><?php if ($error_message !== null): ?><p role="alert"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?><form name="reset-password" method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><div class="row"><i class="fas fa-lock"></i><input type="password" placeholder="Enter password" minlength="8" required name="password" id="password" autocomplete="new-password"></div><div class="row"><i class="fas fa-key"></i><input type="password" placeholder="Confirm password" minlength="8" required name="confirm_password" id="confirm_password" autocomplete="new-password"></div><div class="row button"><input type="submit" value="Reset" name="reset"></div><div class="signup-link">Remembered it? <a href="login.php">Login</a></div></form></div></div></body></html>
