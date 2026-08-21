<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';
$statement = $con->prepare('SELECT id, content FROM notifications WHERE user_id = ? ORDER BY id DESC'); $statement->bind_param('i', $user_id); $statement->execute(); $result = $statement->get_result();
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Notifications | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head><body><?php include 'sidebar.php'; ?><main class="container"><h1>Notifications</h1><?php if ($result->num_rows > 0): $count = 1; while ($row = $result->fetch_assoc()): ?><div class="notifications"><?php echo $count . ') ' . htmlspecialchars((string) $row['content'], ENT_QUOTES, 'UTF-8'); ?></div><?php $count++; endwhile; ?><form action="delete_notifications.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="delete_all" value="1"><button id="save-btn" type="submit"><i class="fas fa-trash-alt"></i> Delete All</button></form><?php else: ?><div class="empty-state"><h2>No notifications</h2><p>You're all caught up.</p></div><?php endif; ?></main><?php $statement->close(); $con->close(); ?></body></html>
