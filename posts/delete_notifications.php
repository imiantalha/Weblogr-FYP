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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_all'])) {
    header('Location: notifications.php');
    exit;
}

require '../database/db.php';
$user_id = (int) $_SESSION['user_id'];

$statement = $con->prepare('DELETE FROM notifications WHERE user_id = ?');
$statement->bind_param('i', $user_id);
$statement->execute();
$statement->close();
$con->close();

header('Location: notifications.php');
exit;
