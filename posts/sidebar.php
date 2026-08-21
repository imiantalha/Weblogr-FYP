<?php
require_once __DIR__ . '/../includes/security.php';
start_secure_session();
$is_admin = ($_SESSION['user_type'] ?? '') === 'Admin';
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Weblogr Navigation</title><script src="index.js" defer></script><link rel="stylesheet" href="../posts/style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head>
<body>
<div class="sidebar">
    <div class="top-bar"><span>Weblogr</span></div>
    <ul class="menu">
        <li><a href="../posts/index.php"><i class="fas fa-home fa-2x" title="Home"></i><span>Home</span></a></li>
        <li><a href="../registration/profile.php"><i class="fas fa-user-circle fa-2x" title="Profile"></i><span>Profile</span></a></li>
        <li><a href="../posts/user_posts.php"><i class="fas fa-file-alt fa-2x" title="My Posts"></i><span>My Posts</span></a></li>
        <li><a href="../posts/new_post.php"><i class="fas fa-plus fa-2x" title="New Post"></i><span>New Post</span></a></li>
        <li><a href="../posts/draft_posts.php"><i class="fas fa-file fa-2x" title="Draft Posts"></i><span>Drafts</span></a></li>
        <?php if ($is_admin): ?>
            <li><a href="../posts/manage_content.php"><i class="fas fa-cog fa-2x" title="Manage Content"></i><span>Manage Content</span></a></li>
            <li><a href="../posts/reports.php"><i class="fas fa-exclamation-triangle fa-2x" title="Reports"></i><span>Reports</span></a></li>
        <?php endif; ?>
        <li><a href="../posts/notifications.php"><i class="fas fa-bell fa-2x" title="Notifications"></i><span>Notifications</span></a></li>
        <li><form action="../posts/logout.php" method="post" class="sidebar-logout"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><button type="submit" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt fa-2x" title="Log Out"></i><span>Log Out</span></button></form></li>
    </ul>
</div>
</body>
</html>
