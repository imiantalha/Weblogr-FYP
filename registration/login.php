<?php

declare(strict_types=1);
require '../includes/security.php';
start_secure_session();
if(isset($_SESSION['username'])){header('Location: profile.php');exit;}
$login_error=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    require '../database/db.php';
    $username=trim((string)($_POST['username']??'')); $password=(string)($_POST['password']??''); $user_type=(string)($_POST['user_type']??'');
    enforce_login_rate_limit($username);
    if($username===''||$password===''||!in_array($user_type,['Common user','Admin'],true)){$login_error=true;record_failed_login($username);}
    else{
        $statement=$con->prepare('SELECT username,password,is_verified,user_type,user_id FROM users WHERE username=? AND user_type=? LIMIT 1');
        $statement->bind_param('ss',$username,$user_type);$statement->execute();$user=$statement->get_result()->fetch_assoc();$statement->close();
        if($user!==null&&(int)$user['is_verified']===1&&password_verify($password,$user['password'])){
            session_regenerate_id(true);$_SESSION['username']=$user['username'];$_SESSION['user_id']=(int)$user['user_id'];$_SESSION['user_type']=$user['user_type'];$_SESSION['csrf_token']=bin2hex(random_bytes(32));clear_login_rate_limit($username);$con->close();header('Location: ../posts/index.php');exit;
        }
        $login_error=true;record_failed_login($username);
    }
    $con->close();
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><div class="center-items"><div class="container"><div class="welcome"><h1>Welcome to Weblogr</h1></div><div class="wrapper"><div class="title">Login Form</div><?php if($login_error):?><p role="alert">Username or password is incorrect, or the account is not verified.</p><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');?>"><div class="row"><i class="fas fa-user"></i><input type="text" placeholder="Username" required name="username" autocomplete="username"></div><div class="row"><i class="fas fa-lock"></i><input type="password" placeholder="Password" required name="password" autocomplete="current-password"></div><div class="row"><select name="user_type" class="row" style="width:100%;text-align:center;font-size:18px;color:#999"><option value="Common user" selected>USER</option><option value="Admin">ADMIN</option></select></div><div class="row button"><input type="submit" value="Login" name="login"><a href="forgot_password.php" style="float:right">Forgot Password?</a><br></div><br><div class="signup-link">Not a member? <a href="signup.php">Signup Now</a></div></form></div></div></div></body></html>
