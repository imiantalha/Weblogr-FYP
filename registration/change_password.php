<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id=require_authentication();
require '../database/db.php';
$message=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $current=(string)($_POST['current_password']??'');$new=(string)($_POST['new_password']??'');$confirm=(string)($_POST['confirm_password']??'');
 if($current===''||$new===''||$confirm==='')$error='All password fields are required.';
 elseif(strlen($new)<8||!preg_match('/[A-Za-z]/',$new)||!preg_match('/\d/',$new))$error='New password must be at least 8 characters and contain a letter and a number.';
 elseif($new!==$confirm)$error='New password and confirmation do not match.';
 else{$s=$con->prepare('SELECT password FROM users WHERE user_id=? LIMIT 1');$s->bind_param('i',$user_id);$s->execute();$user=$s->get_result()->fetch_assoc();$s->close();if(!$user||!password_verify($current,$user['password']))$error='Current password is incorrect.';else{$hash=password_hash($new,PASSWORD_DEFAULT);$s=$con->prepare('UPDATE users SET password=? WHERE user_id=?');$s->bind_param('si',$hash,$user_id);$s->execute();$s->close();session_regenerate_id(true);$_SESSION['csrf_token']=bin2hex(random_bytes(32));$message='Your password has been changed successfully.';}}
}
function e(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Change Password | Weblogr</title><link rel="stylesheet" href="style.css"></head><body><main class="account-shell"><section class="account-card"><p class="eyebrow">ACCOUNT SECURITY</p><h1>Change password</h1><p>Use a strong password you do not reuse elsewhere.</p><?php if($error):?><div class="form-alert" role="alert"><?php echo e($error);?></div><?php elseif($message):?><div class="form-success" role="status"><?php echo e($message);?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo e(csrf_token());?>"><label>Current password<input type="password" name="current_password" autocomplete="current-password" required></label><label>New password<input type="password" name="new_password" minlength="8" autocomplete="new-password" required></label><label>Confirm new password<input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label><div class="form-actions"><a class="secondary-button" href="profile.php">Cancel</a><button class="submit" type="submit">Update password</button></div></form></section></main></body></html><?php $con->close();?>
