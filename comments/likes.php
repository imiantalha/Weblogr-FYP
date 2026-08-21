<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

verify_csrf();
require '../database/db.php';

$blog_id = filter_var($_POST['blog_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$blog_id) {
    http_response_code(422);
    exit('Invalid post.');
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

    $statement = $con->prepare('SELECT 1 FROM post_likes WHERE blog_id = ? AND user_id = ? LIMIT 1');
    $statement->bind_param('ii', $blog_id, $user_id);
    $statement->execute();
    $already_liked = $statement->get_result()->num_rows > 0;
    $statement->close();

    if (!$already_liked) {
        $statement = $con->prepare('INSERT INTO post_likes (blog_id, user_id) VALUES (?, ?)');
        $statement->bind_param('ii', $blog_id, $user_id);
        $statement->execute();
        $statement->close();

        $statement = $con->prepare('UPDATE blogs SET likes = likes + 1 WHERE blog_id = ?');
        $statement->bind_param('i', $blog_id);
        $statement->execute();
        $statement->close();

        $blogger_id = (int) $blog['user_id'];
        if ($blogger_id !== $user_id) {
            $notification_content = "$username likes your post (Blog ID: $blog_id)";
            $statement = $con->prepare('INSERT INTO notifications (content, user_id) VALUES (?, ?)');
            $statement->bind_param('si', $notification_content, $blogger_id);
            $statement->execute();
            $statement->close();
        }
    }

    $statement = $con->prepare('SELECT likes FROM blogs WHERE blog_id = ? LIMIT 1');
    $statement->bind_param('i', $blog_id);
    $statement->execute();
    $likes = (int) $statement->get_result()->fetch_assoc()['likes'];
    $statement->close();

    $con->commit();
    $con->close();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'liked' => true, 'likes' => $likes]);
    exit;
} catch (Throwable $exception) {
    $con->rollback();
    $con->close();
    error_log('Post like failed: ' . $exception->getMessage());
    http_response_code($exception->getMessage() === 'Post not found.' ? 404 : 500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $exception->getMessage() === 'Post not found.' ? 'Post not found.' : 'Unable to like the post.']);
}
