<?php
declare(strict_types=1);
require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';
$s = $con->prepare('SELECT first_name,last_name,bio,profile_picture FROM users WHERE user_id=? LIMIT 1');
$s->bind_param('i', $user_id); $s->execute();
$p = $s->get_result()->fetch_assoc() ?: [];
$s->close();
$full_name = trim((string)($p['first_name'] ?? '') . ' ' . (string)($p['last_name'] ?? ''));
$full_name = $full_name !== '' ? $full_name : 'Your name';
$bio = trim((string)($p['bio'] ?? ''));
$profile_picture = trim((string)($p['profile_picture'] ?? ''));
$profile_picture = $profile_picture !== '' ? basename($profile_picture) : '';
function count_query(mysqli $con, string $sql, int $id): int { $s=$con->prepare($sql); $s->bind_param('i',$id); $s->execute(); $row=$s->get_result()->fetch_assoc(); $s->close(); return (int)($row['total'] ?? 0); }
$post_count = count_query($con, 'SELECT COUNT(*) total FROM blogs WHERE user_id=?', $user_id);
$follower_count = count_query($con, 'SELECT COUNT(*) total FROM followers WHERE blogger_id=?', $user_id);
$following_count = count_query($con, 'SELECT COUNT(*) total FROM followers WHERE follower_id=?', $user_id);
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
$saved=isset($_GET['saved'])&&$_GET['saved']==='1';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#2563eb"><title>My Profile | Weblogr</title><link rel="icon" href="../assets/weblogr-mark.svg" type="image/svg+xml"><link rel="apple-touch-icon" href="../assets/weblogr-mark.svg"><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="../assets/weblogr-product.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"></head><body>
<?php include '../posts/sidebar.php'; ?>
<main class="content"><div class="profile-page">
<?php if($saved): ?><div class="form-success" role="status"><i class="fas fa-circle-check"></i> Your profile has been updated successfully.</div><?php endif; ?>
<section class="profile-card profile-header">
<div class="profile-picture-large"><?php if($profile_picture): ?><img src="<?php echo e('../uploads/'.$profile_picture); ?>" alt="<?php echo e($full_name); ?> profile picture"><?php else: ?><span aria-hidden="true"><?php echo e(strtoupper(substr($full_name,0,1))); ?></span><?php endif; ?></div>
<div class="profile-main"><p class="eyebrow">YOUR PROFILE</p><h1><?php echo e($full_name); ?></h1><p><?php echo e($bio!==''?$bio:'Tell readers a little about yourself.'); ?></p><div class="profile-stats"><div><strong><?php echo $post_count; ?></strong><span>Posts</span></div><div><strong><?php echo $follower_count; ?></strong><span>Followers</span></div><div><strong><?php echo $following_count; ?></strong><span>Following</span></div></div></div>
<div class="profile-buttons"><a class="primary-button" href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit profile</a><a class="secondary-button" href="change_password.php"><i class="fas fa-lock"></i> Security</a></div>
</section>
<section class="profile-links card"><div class="page-header"><div><p class="eyebrow">ACCOUNT</p><h2>Manage your account</h2><p>Keep your profile, security, and publishing identity up to date.</p></div></div>
<a href="edit_profile.php"><i class="fas fa-user-edit"></i><span><strong>Profile details</strong><small>Update your name, bio, and profile picture.</small></span><i class="fas fa-chevron-right"></i></a>
<a href="change_password.php"><i class="fas fa-shield-alt"></i><span><strong>Security</strong><small>Change your password and keep your account protected.</small></span><i class="fas fa-chevron-right"></i></a>
<a href="../posts/user_posts.php"><i class="fas fa-file-alt"></i><span><strong>Your posts</strong><small>View and manage everything you've published.</small></span><i class="fas fa-chevron-right"></i></a>
</section></div></main>
</body></html><?php $con->close();?>
