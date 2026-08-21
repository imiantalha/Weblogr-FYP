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

try {
    if ($blog_id !== false && $blog_id !== null) {
        $con->begin_transaction();

        $statement = $con->prepare('DELETE FROM comments WHERE blog_id = ? AND EXISTS (SELECT 1 FROM blogs WHERE blog_id = ? AND user_id = ?)');
        $statement->bind_param('iii', $blog_id, $blog_id, $user_id);
        $statement->execute();
        $statement->close();

        $statement = $con->prepare('DELETE FROM blogs WHERE blog_id = ? AND user_id = ?');
        $statement->bind_param('ii', $blog_id, $user_id);
        $statement->execute();
        $deleted = $statement->affected_rows;
        $statement->close();

        if ($deleted !== 1) {
            throw new RuntimeException('Post not found or access denied.');
        }

        $con->commit();
        $con->close();
        header('Location: user_posts.php');
        exit;
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

    http_response_code(400);
    echo 'A valid post or draft ID is required.';
} catch (Throwable $exception) {
    if ($con->errno === 0) {
        $con->rollback();
    }
    $con->close();
    error_log('Delete failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Unable to delete the requested content.';
}
