<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../registration/login.php');
    exit;
}

require '../database/db.php';

$user_id = (int) $_SESSION['user_id'];
$blog_id = filter_input(INPUT_GET, 'blog_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$draft_id = filter_input(INPUT_GET, 'draft_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$statement = $con->prepare('SELECT user_type FROM users WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $user_id);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();
$statement->close();

if ($user === null) {
    $con->close();
    http_response_code(403);
    exit('Access denied.');
}

$is_admin = $user['user_type'] === 'Admin';

if ($blog_id !== false && $blog_id !== null) {
    try {
        $con->begin_transaction();

        $statement = $con->prepare('SELECT user_id FROM blogs WHERE blog_id = ? LIMIT 1');
        $statement->bind_param('i', $blog_id);
        $statement->execute();
        $post = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($post === null || (!$is_admin && (int) $post['user_id'] !== $user_id)) {
            throw new RuntimeException('Post not found or access denied.');
        }

        $statement = $con->prepare('DELETE FROM comments WHERE blog_id = ?');
        $statement->bind_param('i', $blog_id);
        $statement->execute();
        $statement->close();

        $statement = $con->prepare('DELETE FROM blogs WHERE blog_id = ?');
        $statement->bind_param('i', $blog_id);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();

        if ($deleted !== 1) {
            throw new RuntimeException('Post not found or access denied.');
        }

        $con->commit();
        $con->close();
        header('Location: ' . ($is_admin ? 'manage_content.php' : 'user_posts.php'));
        exit;
    } catch (Throwable $exception) {
        $con->rollback();
        $con->close();
        error_log('Delete post failed: ' . $exception->getMessage());
        http_response_code($exception->getMessage() === 'Post not found or access denied.' ? 404 : 500);
        echo $exception->getMessage() === 'Post not found or access denied.'
            ? 'Post not found or access denied.'
            : 'Unable to delete the post.';
        exit;
    }
}

if ($draft_id !== false && $draft_id !== null) {
    $statement = $con->prepare('DELETE FROM draft_posts WHERE draft_id = ? AND user_id = ?');
    $statement->bind_param('ii', $draft_id, $user_id);
    $statement->execute();
    $deleted = $statement->affected_rows;
    $statement->close();
    $con->close();

    if ($deleted !== 1) {
        http_response_code(404);
        exit('Draft not found or access denied.');
    }

    header('Location: draft_posts.php');
    exit;
}

$con->close();
http_response_code(400);
echo 'A valid post or draft ID is required.';
