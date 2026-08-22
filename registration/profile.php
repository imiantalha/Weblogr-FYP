<?php
declare(strict_types=1);
require '../includes/security.php';
$user_id=require_authentication();
require '../database/db.php';
$s=$con->prepare('SELECT first_name,last_name,bio,profile_picture FROM users WHERE user_id=? LIMIT 1');
$s->bind_param('i',$user_id);$s->execute();$p=$s->get_result()->fetch_assoc();$s->close();
$full_name=trim((string)($p['first_name']??'').' '.(string)($p['last_name']??''));
$full_name=$full_name!==''?$full_name:'Your name';
$bio=trim((string)($p['bio']??''));
$profile_picture=!empty($p['profile_picture'])?$p['profile_picture']:'logo.PNG';
function count_query(mysqli $con,string $sql,int $id):int{$s=$con->prepare($sql);$s->bind_param('i',$id);$s->execute();$n=(int)$s->get_result()->fetch_assoc()['total'];$s->close();return $n;}
$post_count=count_query($con,'SELECT COUNT(*) total FROM blogs WHERE user_id=?',$user_id);
$follower_count=count_query($con,'SELECT COUNT(*) total FROM followers WHERE blogger_id=?',$user_id);
$following_count=count_query($con,'SELECT COUNT(*) total FROM followers WHERE follower_id=?',$user_id);
function e(string $v):string{return htmlspecialchars($v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Profile | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body>
<?php include '../posts/sidebar.php'; ?>
<main class="content"><div class="profile-page">
<section class="profile-card">
<div class="profile-picture-large"><img src="<?php echo e('../uploads/'.$profile_picture); ?>" alt="Profile picture"></div>
<div class="profile-main"><p class="eyebrow">YOUR PROFILE</p><h1><?php echo e($full_name); ?></h1><p><?php echo e($bio!==''?$bio:'Tell readers a little about yourself.'); ?></p><div class="profile-stats"><div><strong><?php echo $post_count; ?></strong><span>Posts</span></div><div><strong><?php echo $follower_count; ?></strong><span>Followers</span></div><div><strong><?php echo $following_count; ?></strong><span>Following</span></div></div></div>
<div class="profile-buttons"><a class="submit" href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit profile</a><a class="secondary-button" href="change_password.php"><i class="fas fa-lock"></i> Password</a></div>
</section>
<section class="profile-links"><p class="eyebrow">ACCOUNT</p><h2>Manage your account</h2>
<a href="edit_profile.php"><i class="fas fa-user-edit"></i><span><strong>Profile details</strong><small>Update your name, bio, and profile picture.</small></span><i class="fas fa-chevron-right"></i></a>
<a href="change_password.php"><i class="fas fa-shield-alt"></i><span><strong>Security</strong><small>Change your password and keep your account protected.</small></span><i class="fas fa-chevron-right"></i></a>
<a href="../posts/user_posts.php"><i class="fas fa-file-alt"></i><span><strong>Your posts</strong><small>View and manage everything you've published.</small></span><i class="fas fa-chevron-right"></i></a>
</section></div></main>
</body></html><?php $con->close();?>
