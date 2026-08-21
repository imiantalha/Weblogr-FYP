<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';

$statement = $con->prepare('SELECT id, content FROM notifications WHERE user_id = ? ORDER BY id DESC');
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Notifications | Weblogr</title>
<link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="content">
    <section class="page-header">
        <div><p class="eyebrow">ACTIVITY</p><h1>Notifications</h1><p>Stay up to date with activity around your posts and profile.</p></div>
        <?php if ($result->num_rows > 0): ?><form action="delete_notifications.php" method="post" onsubmit="return confirm('Delete all notifications?');"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="delete_all" value="1"><button class="secondary-button" type="submit"><i class="fas fa-trash-alt"></i> Clear all</button></form><?php endif; ?>
    </section>
    <section class="notifications-list">
    <?php if ($result->num_rows > 0): $count = 1; while ($row = $result->fetch_assoc()): ?>
        <article class="notification-card"><span class="notification-number"><?php echo $count; ?></span><p><?php echo htmlspecialchars((string)$row['content'], ENT_QUOTES, 'UTF-8'); ?></p></article>
    <?php $count++; endwhile; else: ?>
        <div class="empty-state"><div class="empty-icon"><i class="far fa-bell"></i></div><h2>You're all caught up</h2><p>New activity will appear here.</p></div>
    <?php endif; ?>
    </section>
</main>
<?php $statement->close(); $con->close(); ?>
</body>
</html>
