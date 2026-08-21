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

$statement = $con->prepare('SELECT id, content FROM notifications WHERE user_id = ? ORDER BY id DESC');
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <h1>Notifications</h1>
    <?php if ($result->num_rows > 0): ?>
        <?php $count = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="notifications">
                <?php echo $count . ') ' . htmlspecialchars((string) $row['content'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php $count++; ?>
        <?php endwhile; ?>

        <form action="delete_notifications.php" method="post">
            <button id="save-btn" type="submit" name="delete_all"><i class="fas fa-trash-alt fa-2x" title="Delete All"></i></button>
        </form>
    <?php else: ?>
        <div style="text-align: center;">No notification found.</div>
    <?php endif; ?>

    <?php
    $statement->close();
    $con->close();
    ?>
</body>
</html>
