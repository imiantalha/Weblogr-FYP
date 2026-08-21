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

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($user_id === false || $user_id === null) {
    http_response_code(400);
    exit('A valid user ID is required.');
}

$statement = $con->prepare('SELECT username FROM users WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $user_id);
$statement->execute();
$poster = $statement->get_result()->fetch_assoc();
$statement->close();

if ($poster === null) {
    $con->close();
    http_response_code(404);
    exit('User not found.');
}

$statement = $con->prepare('SELECT blog_id, title, created_date, image, description, user_id FROM blogs WHERE user_id = ? ORDER BY created_date DESC');
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Poster Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="all-posts-container">
        <h1>Welcome <?php echo htmlspecialchars(strtoupper((string) $poster['username']), ENT_QUOTES, 'UTF-8'); ?></h1>
        <button id="save-btn"><a href="follow.php?user_id=<?php echo (int) $user_id; ?>">Follow Me</a></button><br>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="post-container">
                    <span id="display-title"><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></span><br>
                    <div class="date-container">
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $row['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span><br><br>
                    </div>
                    <?php if (!empty($row['image'])): ?>
                        <img id="display-image" src="../images/<?php echo htmlspecialchars((string) $row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Post image"><br>
                    <?php endif; ?>
                    <p id="display-para"><?php echo htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="like-button">
                        <a href="../comments/likes.php?blog_id=<?php echo (int) $row['blog_id']; ?>"><i class="fas fa-thumbs-up fa-2x" title="Like"></i></a>
                        <a href="../comments/comments.php?blog_id=<?php echo (int) $row['blog_id']; ?>" style="margin-left:15px"><i class="fas fa-comment fa-2x" title="Comment"></i></a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>

    <?php
    $statement->close();
    $con->close();
    ?>
</body>
</html>
