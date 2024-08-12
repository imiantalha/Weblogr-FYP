<?php
    $username = $_SESSION["username"];
    if(isset($username) && $username == 'admin') {
        $manage_posts = '<li><a href="../posts/manage_content.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-cog fa-2x" title="Manage Content"></i></a></li>';
        $manage_reports = '<li><a href="../posts/reports.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-exclamation-triangle fa-2x" title="Reports"></i></i></a></li>';
    } else {
        $manage_posts = "";
        $manage_reports = "";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="index.js"></script>
    <link rel="stylesheet" href="../posts/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
    <div class="sidebar">
        <div class="top-bar">
            <span>Weblogr</span> 
        </div>
        <ul class="menu">
            <li><a href="../posts/index.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-home fa-2x" title="Home"></i></a></li>
            <li><a href='../registration/profile.php' style="display:inline-block; margin-left:5px;"><i class="fas fa-user-circle fa-2x" title="Profile"></i></a></li>
            <li><a href="../posts/user_posts.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-file-alt fa-2x" title="My Posts"></i></a></li>
            <li><a href='../posts/new_post.php' style="display:inline-block; margin-left:5px;"><i class="fas fa-plus fa-2x" title="New Post"></i></a></li>
            <li><a href="../posts/draft_posts.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-file fa-2x" title="Draft Posts"></i></a></li>
            <?php echo $manage_posts ?>
            <?php echo $manage_reports ?>
            <li><a href="../posts/notifications.php" style="display:inline-block; margin-left:5px;"><i class="fas fa-bell fa-2x" title="Notifications"></i></a></li>
            <li><a href="logout.php" style="display:inline-block; margin-left:5px;" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt fa-2x" title="Log Out"></i></a></li>
        </ul>
    </div>
</body>
</html>