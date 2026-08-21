<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

$blog_id = filter_var($_GET['blog_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$blog_id) {
    $con->close();
    http_response_code(400);
    exit('A valid blog ID is required.');
}

$statement = $con->prepare('SELECT blog_id, title, created_date, image, description, likes FROM blogs WHERE blog_id = ? LIMIT 1');
$statement->bind_param('i', $blog_id);
$statement->execute();
$blog = $statement->get_result()->fetch_assoc();
$statement->close();

if ($blog === null) {
    $con->close();
    http_response_code(404);
    exit('Blog post not found.');
}

$statement = $con->prepare('SELECT comment_id, blog_id, commenter_id, comment_text, likes, comment_date FROM comments WHERE blog_id = ? ORDER BY comment_date ASC');
$statement->bind_param('i', $blog_id);
$statement->execute();
$comments = $statement->get_result();
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
<title>Comments | Weblogr</title>
</head>
<body>
<?php include '../posts/sidebar.php'; ?>
<main class="container">
    <a href="../posts/index.php"><b>← Back to posts</b></a>
    <article class="post-container">
        <span id="display-title"><?php echo htmlspecialchars((string)$blog['title'], ENT_QUOTES, 'UTF-8'); ?></span>
        <div class="date-container"><span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string)$blog['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span><span><?php echo (int)$blog['likes']; ?> likes</span></div>
        <?php if (!empty($blog['image'])): ?><img id="display-image" src="../images/<?php echo rawurlencode((string)$blog['image']); ?>" alt="Post image"><?php endif; ?>
        <p id="display-para"><?php echo nl2br(htmlspecialchars((string)$blog['description'], ENT_QUOTES, 'UTF-8')); ?></p>
    </article>

    <section class="comments-section">
        <h2>Join the conversation</h2>
        <form action="save_comment.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
            <input type="hidden" name="blog_id" value="<?php echo (int)$blog_id; ?>">
            <textarea id="blog-para" name="comment_text" rows="4" maxlength="2000" required placeholder="Write a thoughtful comment..."></textarea>
            <button id="save-btn" type="submit">Post Comment</button>
        </form>

        <div class="comments-list">
        <?php if ($comments->num_rows > 0): ?>
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <article class="comment-item">
                    <p><?php echo nl2br(htmlspecialchars((string)$comment['comment_text'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <div class="comment-actions">
                        <span><?php echo (int)$comment['likes']; ?> likes</span>
                        <form action="like_a_comment.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="comment_id" value="<?php echo (int)$comment['comment_id']; ?>">
                            <input type="hidden" name="blog_id" value="<?php echo (int)$comment['blog_id']; ?>">
                            <button class="like-btn" type="submit" aria-label="Like comment"><i class="fas fa-thumbs-up"></i></button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-comments"><h3>No comments yet</h3><p>Be the first to start the conversation.</p></div>
        <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
<?php $statement->close(); $con->close(); ?>
