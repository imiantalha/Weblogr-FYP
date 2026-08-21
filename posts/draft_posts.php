<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

$user_id = (int) $_SESSION['user_id'];
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

$statement = $con->prepare('SELECT draft_id, title, created_date, image, description, category FROM draft_posts WHERE user_id = ? ORDER BY created_date DESC');
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Posts | Weblogr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="top-bar"><span>Draft Posts</span></div>
<main class="content">
    <div class="all-posts-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="post-container">
                    <span id="display-title"><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="date-container">
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $row['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($row['category'])): ?><span><?php echo htmlspecialchars((string) $row['category'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                    </div>
                    <?php if (!empty($row['image'])): ?>
                        <img id="display-image" src="../images/<?php echo rawurlencode((string) $row['image']); ?>" alt="<?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endif; ?>
                    <p id="display-para"><?php echo nl2br(htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <div class="like-button">
                        <a href="edit_draft.php?draft_id=<?php echo (int) $row['draft_id']; ?>" aria-label="Edit draft"><i class="fas fa-edit" title="Edit"></i></a>
                        <form action="delete_post.php" method="post" style="display:inline; margin-left:15px" onsubmit="return confirmDelete();">
                            <input type="hidden" name="draft_id" value="<?php echo (int) $row['draft_id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                            <button type="submit" class="icon-button" aria-label="Delete draft"><i class="fas fa-trash-alt" title="Delete"></i></button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No drafts yet</h2>
                <p>Save an unfinished post here and come back to it later.</p>
                <a href="new_post.php" class="submit">Create Draft</a>
            </div>
        <?php endif; ?>
    </div>
</main>
<script>
function confirmDelete() { return confirm('Are you sure you want to delete this draft?'); }
</script>
<?php $statement->close(); $con->close(); ?>
</body>
</html>
