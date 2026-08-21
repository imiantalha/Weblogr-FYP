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

if ($blog_id === false || $blog_id === null) {
    $con->close();
    http_response_code(400);
    exit('A valid blog ID is required.');
}

$statement = $con->prepare('SELECT blog_id, title, created_date, image, description FROM blogs WHERE blog_id = ? LIMIT 1');
$statement->bind_param('i', $blog_id);
$statement->execute();
$blog = $statement->get_result()->fetch_assoc();
$statement->close();

if ($blog === null) {
    $con->close();
    http_response_code(404);
    exit('Blog post not found.');
}

$statement = $con->prepare(
    'SELECT comment_id, blog_id, comment_text FROM comments WHERE blog_id = ? ORDER BY comment_date ASC'
);
$statement->bind_param('i', $blog_id);
$statement->execute();
$comments = $statement->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <title>Comments</title>
</head>
<body>
    <div class="container">
        <a href="../posts/index.php"><b>Back</b></a><br>

        <div class="post-container">
            <span id="display-title"><?php echo htmlspecialchars((string) $blog['title'], ENT_QUOTES, 'UTF-8'); ?></span><br>
            <div class="date-container">
                <span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $blog['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span><br><br>
            </div>
            <?php if (!empty($blog['image'])): ?>
                <img id="display-image" src="../images/<?php echo htmlspecialchars((string) $blog['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Post image"><br>
            <?php endif; ?>
            <p id="display-para"><?php echo htmlspecialchars((string) $blog['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <form action="save_comment.php" method="post">
            <input type="hidden" name="blog_id" value="<?php echo (int) $blog_id; ?>">
            <textarea id="blog-para" name="comment_text" cols="40" rows="2" maxlength="2000" required placeholder="Comment..."></textarea><br>
            <button id="save-btn" type="submit">Save Comment</button>
        </form>

        <?php if ($comments->num_rows > 0): ?>
            <h3>Comments:</h3>
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div>
                    <p style="display: inline;">
                        <?php echo htmlspecialchars((string) $comment['comment_text'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <form action="like_a_comment.php" method="post" style="display: inline; margin-left: 10px;">
                        <input type="hidden" name="comment_id" value="<?php echo (int) $comment['comment_id']; ?>">
                        <input type="hidden" name="blog_id" value="<?php echo (int) $comment['blog_id']; ?>">
                        <button class="like-btn" type="submit" title="Like">
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No comments yet.</p>
        <?php endif; ?>
    </div>

    <?php
    $statement->close();
    $con->close();
    ?>
</body>
</html>
