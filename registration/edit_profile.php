<?php
declare(strict_types=1);
require '../includes/security.php';
$user_id=require_authentication();
require '../database/db.php';
$error=null;
$profile=['first_name'=>'','last_name'=>'','bio'=>'','profile_picture'=>''];
$statement=$con->prepare('SELECT first_name,last_name,bio,profile_picture FROM users WHERE user_id=? LIMIT 1');
$statement->bind_param('i',$user_id);$statement->execute();$existing=$statement->get_result()->fetch_assoc();$statement->close();
if($existing)$profile=$existing;
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $first_name=trim((string)($_POST['first_name']??''));$last_name=trim((string)($_POST['last_name']??''));$bio=trim((string)($_POST['bio']??''));
 if($first_name===''||mb_strlen($first_name)>50||$last_name===''||mb_strlen($last_name)>50)$error='Please enter a valid first and last name.';
 elseif(mb_strlen($bio)>1000)$error='Bio must be 1,000 characters or fewer.';
 else{
  $profile_picture=(string)($profile['profile_picture']??'');$new_file=null;$destination=null;
  if(isset($_FILES['profile_picture'])&&$_FILES['profile_picture']['error']!==UPLOAD_ERR_NO_FILE){
   $file=$_FILES['profile_picture'];
   if($file['error']!==UPLOAD_ERR_OK)$error='We could not upload that image. Please choose the file again.';
   elseif((int)$file['size']>5*1024*1024)$error='Profile image must be 5 MB or smaller.';
   elseif(!is_uploaded_file($file['tmp_name']))$error='The uploaded image could not be verified. Please try again.';
   else{
    $mime=function_exists('finfo_open')?(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']):mime_content_type($file['tmp_name']);
    $extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
    if(!isset($extensions[$mime]))$error='Only JPG, PNG, GIF, and WebP images are allowed.';
    else{
     $upload_dir=dirname(__DIR__).'/uploads';
     if(!is_dir($upload_dir)&&!mkdir($upload_dir,0755,true)&&!is_dir($upload_dir))$error='Profile image storage is not available right now.';
     else{$new_file=bin2hex(random_bytes(16)).'.'.$extensions[$mime];$destination=$upload_dir.'/'.$new_file;if(!move_uploaded_file($file['tmp_name'],$destination))$error='Unable to save the profile image right now. Please try again.';else$profile_picture=$new_file;}
    }
   }
  }
  if($error===null){$statement=$con->prepare('UPDATE users SET first_name=?,last_name=?,bio=?,profile_picture=? WHERE user_id=?');$statement->bind_param('ssssi',$first_name,$last_name,$bio,$profile_picture,$user_id);if(!$statement->execute())$error='We could not save your profile changes. Please try again.';$statement->close();}
  if($error===null){header('Location: profile.php?saved=1');exit;}
  if($new_file!==null&&$destination!==null&&is_file($destination))unlink($destination);
 }
}
$csrf=htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8');
function e(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#2563eb"><title>Edit Profile | Weblogr</title><link rel="icon" href="../assets/weblogr-mark.svg" type="image/svg+xml"><link rel="apple-touch-icon" href="../assets/weblogr-mark.svg"><script src="../assets/form-validation.js" defer></script><link rel="stylesheet" href="../posts/style.css"><link rel="stylesheet" href="../assets/responsive.css"><link rel="stylesheet" href="../assets/weblogr-product.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></head><body>
<?php include '../posts/sidebar.php'; ?>
<main class="content"><div class="profile-form-card"><section class="account-card">
<div class="account-card-header"><div class="header-copy"><p class="eyebrow">ACCOUNT</p><h1>Edit profile</h1><p>Keep your public author profile accurate and recognizable.</p></div><a class="secondary-button" href="profile.php"><i class="fas fa-arrow-left"></i> Back</a></div>
<?php if($error): ?><div class="form-alert" role="alert"><i class="fas fa-circle-exclamation"></i> <?php echo e($error); ?></div><?php endif; ?>
<form class="profile-form" action="" method="post" enctype="multipart/form-data" novalidate><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
<div class="profile-form-grid"><label>First name<input type="text" name="first_name" maxlength="50" value="<?php echo e((string)$profile['first_name']); ?>" required data-label="First name" autocomplete="given-name"></label><label>Last name<input type="text" name="last_name" maxlength="50" value="<?php echo e((string)$profile['last_name']); ?>" required data-label="Last name" autocomplete="family-name"></label>
<label class="form-field-full">Bio<textarea name="bio" maxlength="1000" data-label="Bio"><?php echo e((string)$profile['bio']); ?></textarea><span class="field-hint">Up to 1,000 characters.</span></label>
<label class="form-field-full">Profile picture<input type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp"><span class="field-hint">JPG, PNG, GIF or WebP · maximum 5 MB.</span></label></div>
<div class="form-actions"><a class="secondary-button" href="profile.php">Cancel</a><button class="submit" type="submit"><i class="fas fa-save"></i> Save changes</button></div></form>
</section></div></main></body></html>
