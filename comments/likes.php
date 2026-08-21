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

$blog_id = filter_input(INPUT_GET, 'blog_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($blog_id === false || $blog_id === null) {
    header('Location: ../posts/index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$username = strtoupper((string) $_SESSION['username']);

try {
    $con->begin_transaction();

    $statement = $con->prepare('SELECT user_id FROM blogs WHERE blog_id = ? LIMIT 1');
    $statement->bind_param('i', $blog_id);
    $statement->execute();
    $blog = $statement->get_result()->fetch_assoc();
    $statement->close();

    if ($blog === null) {
        throw new RuntimeException('Post not found.');
    }

    $statement = $con->prepare('UPDATE blogs SET likes = likes + 1 WHERE blog_id = ?');
    $statement->bind_param('i', $blog_id);
    $statement->execute();
    $statement->close();

    $blogger_id = (int) $blog['user_id'];
    $notification_content = "$username likes your post (Blog ID: $blog_id)";
    $statement = $con->prepare('INSERT INTO notifications (content, user_id) VALUES (?, ?)');
    $statement->bind_param('si', $notification_content, $blogger_id);
    $statement->execute();
    $statement->close();

    $con->commit();
    $con->close();
    header('Location: ../posts/index.php');
    exit;
} catch (Throwable $exception) {
    $con->rollback();
    $con->close();
    error_log('Post like failed: ' . $exception->getMessage());
    http_response_code($exception->getMessage() === 'Post not found.' ? 404 : 500);
    echo $exception->getMessage() === 'Post not found.' ? 'Post not found.' : 'Unable to like the post.';
}
