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

$follower_id = (int) $_SESSION['user_id'];
$username = strtoupper((string) $_SESSION['username']);
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($user_id === false || $user_id === null || $user_id === $follower_id) {
    header('Location: index.php');
    exit;
}

try {
    $con->begin_transaction();

    $target = $con->prepare('SELECT user_id FROM users WHERE user_id = ? LIMIT 1');
    $target->bind_param('i', $user_id);
    $target->execute();
    $target_exists = $target->get_result()->num_rows === 1;
    $target->close();

    if (!$target_exists) {
        throw new RuntimeException('User not found.');
    }

    $check = $con->prepare('SELECT 1 FROM followers WHERE blogger_id = ? AND follower_id = ? LIMIT 1');
    $check->bind_param('ii', $user_id, $follower_id);
    $check->execute();
    $already_following = $check->get_result()->num_rows === 1;
    $check->close();

    if (!$already_following) {
        $follow = $con->prepare('INSERT INTO followers (blogger_id, follower_id) VALUES (?, ?)');
        $follow->bind_param('ii', $user_id, $follower_id);
        $follow->execute();
        $follow->close();

        $notification_content = "$username started following you.";
        $notification = $con->prepare('INSERT INTO notifications (content, user_id) VALUES (?, ?)');
        $notification->bind_param('si', $notification_content, $user_id);
        $notification->execute();
        $notification->close();
    }

    $con->commit();
    $con->close();
    header('Location: blog_poster.php?user_id=' . $user_id);
    exit;
} catch (Throwable $exception) {
    $con->rollback();
    $con->close();
    error_log('Follow failed: ' . $exception->getMessage());
    http_response_code($exception->getMessage() === 'User not found.' ? 404 : 500);
    echo $exception->getMessage() === 'User not found.' ? 'User not found.' : 'Unable to follow the user.';
}
