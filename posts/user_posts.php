<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$user_id = (int) $_SESSION['user_id'];
$csrf = e(csrf_token());

$statement = $con->prepare('SELECT blog_id, title, created_date, image, description FROM blogs WHERE user_id = ? ORDER BY created_date DESC');
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts | Weblogr</title>
    <script src="index.js" defer></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="top-bar"><span>My Posts</span></div>

<main class="content">
    <div class="all-posts-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="post-container">
                    <span id="display-title"><?php echo e((string) $row['title']); ?></span>
                    <div class="date-container">
                        <span><?php echo e(date('d/m/Y', strtotime((string) $row['created_date']))); ?></span>
                        <span>Blog ID: <?php echo (int) $row['blog_id']; ?></span>
                    </div>

                    <?php if (!empty($row['image'])): ?>
                        <img id="display-image" src="../images/<?php echo rawurlencode((string) $row['image']); ?>" alt="<?php echo e((string) $row['title']); ?>">
                    <?php endif; ?>

                    <p id="display-para"><?php echo nl2br(e((string) $row['description'])); ?></p>

                    <div class="like-button">
                        <a href="edit_post.php?blog_id=<?php echo (int) $row['blog_id']; ?>" aria-label="Edit post">
                            <i class="fas fa-edit" title="Edit"></i>
                        </a>
                        <form action="delete_post.php" method="post" style="display:inline; margin-left:15px" onsubmit="return confirmDelete();">
                            <input type="hidden" name="blog_id" value="<?php echo (int) $row['blog_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                            <button type="submit" class="icon-button" aria-label="Delete post">
                                <i class="fas fa-trash-alt" title="Delete"></i>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No posts yet</h2>
                <p>Share your first story with the Weblogr community.</p>
                <a href="new_post.php" class="submit">Create Post</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php $statement->close(); $con->close(); ?>
</body>
</html>
