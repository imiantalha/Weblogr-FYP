<?php

declare(strict_types=1);

function create_notification(mysqli $con, int $user_id, string $content): void
{
    if ($user_id <= 0 || trim($content) === '') return;
    $statement = $con->prepare('INSERT INTO notifications (content, user_id, is_read, created_at) VALUES (?, ?, 0, NOW())');
    $statement->bind_param('si', $content, $user_id);
    $statement->execute();
    $statement->close();
}

function unread_notification_count(mysqli $con, int $user_id): int
{
    $statement = $con->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
    $statement->bind_param('i', $user_id);
    $statement->execute();
    $count = (int) $statement->get_result()->fetch_assoc()['total'];
    $statement->close();
    return $count;
}
