<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';
require '../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'read_all') {
        $statement = $con->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $statement->bind_param('i', $user_id); $statement->execute(); $statement->close();
    } elseif ($action === 'read' && isset($_POST['notification_id'])) {
        $notification_id = (int)$_POST['notification_id'];
        $statement = $con->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $statement->bind_param('ii', $notification_id, $user_id); $statement->execute(); $statement->close();
    }
    header('Location: notifications.php'); exit;
}

$statement = $con->prepare('SELECT id, content, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 100');
$statement->bind_param('i', $user_id); $statement->execute(); $result = $statement->get_result();
$unread = unread_notification_count($con, $user_id); $csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Notifications | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><?php include 'sidebar.php'; ?><main class="content"><section class="page-header"><div><p class="eyebrow">ACTIVITY</p><h1>Notifications <?php if($unread):?><span class="notification-badge"><?php echo $unread;?></span><?php endif;?></h1><p>Stay up to date with activity around your posts and profile.</p></div><?php if($unread):?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo $csrf;?>"><input type="hidden" name="action" value="read_all"><button class="secondary-button" type="submit"><i class="fas fa-check-double"></i> Mark all read</button></form><?php endif;?></section><section class="notifications-list"><?php if($result->num_rows):while($row=$result->fetch_assoc()):?><article class="notification-card <?php echo (int)$row['is_read']===0?'is-unread':'';?>"><span class="notification-icon"><i class="fas fa-bell"></i></span><div><p><?php echo e((string)$row['content']);?></p><small><?php echo e(date('d M Y, H:i',strtotime((string)$row['created_at'])));?></small></div><?php if((int)$row['is_read']===0):?><form method="post"><input type="hidden" name="csrf_token" value="<?php echo $csrf;?>"><input type="hidden" name="action" value="read"><input type="hidden" name="notification_id" value="<?php echo (int)$row['id'];?>"><button class="notification-read" title="Mark as read" aria-label="Mark notification as read"><i class="fas fa-check"></i></button></form><?php endif;?></article><?php endwhile;else:?><div class="empty-state"><div class="empty-icon"><i class="far fa-bell"></i></div><h2>You're all caught up</h2><p>New activity will appear here.</p></div><?php endif;?></section></main><?php $statement->close();$con->close();?></body></html>
