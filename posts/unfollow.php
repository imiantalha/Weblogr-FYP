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

$follower_id = (int) $_SESSION['user_id'];
$user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$user_id || $user_id === $follower_id) {
    $con->close();
    http_response_code(422);
    exit('Invalid user.');
}

$statement = $con->prepare('DELETE FROM followers WHERE blogger_id = ? AND follower_id = ?');
$statement->bind_param('ii', $user_id, $follower_id);
$statement->execute();
$statement->close();
$con->close();

header('Location: blog_poster.php?user_id=' . $user_id);
exit;
