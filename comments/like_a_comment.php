<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header('Location: ../registration/login.php');
    exit;
}

require '../database/db.php';

$comment_id = filter_var($_POST['comment_id'] ?? 0, FILTER_VALIDATE_INT);
$blog_id = filter_var($_POST['blog_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$comment_id || !$blog_id) {
    http_response_code(400);
    exit('Invalid comment or post ID.');
}

$username = strtoupper((string) $_SESSION['username']);

try {
    $con->begin_transaction();

    $statement = $con->prepare('SELECT commenter_id, comment_text FROM comments WHERE comment_id = ? AND blog_id = ? LIMIT 1');
    $statement->bind_param('ii', $comment_id, $blog_id);
    $statement->execute();
    $comment = $statement->get_result()->fetch_assoc();
    $statement->close();

    if ($comment === null) {
        throw new RuntimeException('Comment not found.');
    }

    $statement = $con->prepare('UPDATE comments SET likes = likes + 1 WHERE comment_id = ? AND blog_id = ?');
    $statement->bind_param('ii', $comment_id, $blog_id);
    $statement->execute();
    $statement->close();

    $notification_content = "$username likes your comment: " . $comment['comment_text'];
    $commenter_id = (int) $comment['commenter_id'];
    $statement = $con->prepare('INSERT INTO notifications (content, user_id) VALUES (?, ?)');
    $statement->bind_param('si', $notification_content, $commenter_id);
    $statement->execute();
    $statement->close();

    $con->commit();
    $con->close();
    header('Location: comments.php?blog_id=' . $blog_id);
    exit;
} catch (Throwable $exception) {
    $con->rollback();
    $con->close();
    error_log('Comment like failed: ' . $exception->getMessage());
    http_response_code($exception->getMessage() === 'Comment not found.' ? 404 : 500);
    echo $exception->getMessage() === 'Comment not found.' ? 'Comment not found.' : 'Unable to like the comment.';
}
