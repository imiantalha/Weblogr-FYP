<?php
require_once __DIR__ . '/../includes/security.php';
start_secure_session();
$is_admin = ($_SESSION['user_type'] ?? '') === 'Admin';
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$notification_count = 0;
if (isset($con) && isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/notifications.php';
    $notification_count = unread_notification_count($con, (int)$_SESSION['user_id']);
}
?>
<div class="sidebar"><div class="top-bar"><span>Weblogr</span></div><ul class="menu"><li><a href="../posts/index.php"><i class="fas fa-home fa-2x"></i><span>Home</span></a></li><li><a href="../registration/profile.php"><i class="fas fa-user-circle fa-2x"></i><span>Profile</span></a></li><li><a href="../posts/user_posts.php"><i class="fas fa-file-alt fa-2x"></i><span>My Posts</span></a></li><li><a href="../posts/following.php"><i class="fas fa-rss fa-2x"></i><span>Following</span></a></li><li><a href="../posts/new_post.php"><i class="fas fa-plus fa-2x"></i><span>New Post</span></a></li><li><a href="../posts/draft_posts.php"><i class="fas fa-file fa-2x"></i><span>Drafts</span></a></li><?php if($is_admin):?><li class="menu-divider"></li><li class="menu-label">ADMIN</li><li><a href="../posts/admin_dashboard.php"><i class="fas fa-chart-line fa-2x"></i><span>Dashboard</span></a></li><li><a href="../posts/admin_users.php"><i class="fas fa-users-cog fa-2x"></i><span>Users</span></a></li><li><a href="../posts/manage_content.php"><i class="fas fa-cog fa-2x"></i><span>Manage Content</span></a></li><li><a href="../posts/reports.php"><i class="fas fa-exclamation-triangle fa-2x"></i><span>Reports</span></a></li><?php endif;?><li><a href="../posts/notifications.php"><i class="fas fa-bell fa-2x"></i><span>Notifications</span><?php if($notification_count>0):?><b class="sidebar-badge"><?php echo $notification_count>99?'99+':$notification_count;?></b><?php endif;?></a></li><li><form action="../posts/logout.php" method="post" class="sidebar-logout"><input type="hidden" name="csrf_token" value="<?php echo $csrf;?>"><button type="submit" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt fa-2x"></i><span>Log Out</span></button></form></li></ul></div>
