<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

$user_id = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$user_id) {
    http_response_code(400);
    exit('A valid user ID is required.');
}

$viewer_id = (int) $_SESSION['user_id'];
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

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

$statement = $con->prepare('SELECT 1 FROM followers WHERE blogger_id = ? AND follower_id = ? LIMIT 1');
$statement->bind_param('ii', $user_id, $viewer_id);
$statement->execute();
$is_following = $statement->get_result()->num_rows === 1;
$statement->close();

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
    <title><?php echo htmlspecialchars(strtoupper((string) $poster['username']), ENT_QUOTES, 'UTF-8'); ?> | Weblogr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="content">
    <section class="profile-header">
        <div>
            <p class="eyebrow">BLOGGER PROFILE</p>
            <h1><?php echo htmlspecialchars(strtoupper((string) $poster['username']), ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Explore their latest posts and follow their writing.</p>
        </div>
        <?php if ($user_id !== $viewer_id): ?>
            <?php if ($is_following): ?>
                <form action="unfollow.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo (int) $user_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                    <button class="secondary-button" type="submit">Following</button>
                </form>
            <?php else: ?>
                <form action="follow.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo (int) $user_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
                    <button class="submit" type="submit">Follow</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <div class="all-posts-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="post-container">
                    <span id="display-title"><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div class="date-container">
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $row['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <?php if (!empty($row['image'])): ?>
                        <img id="display-image" src="../images/<?php echo rawurlencode((string) $row['image']); ?>" alt="<?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php endif; ?>
                    <p id="display-para"><?php echo nl2br(htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <div class="like-button">
                        <a href="../comments/comments.php?blog_id=<?php echo (int) $row['blog_id']; ?>" aria-label="View comments">
                            <i class="fas fa-comment fa-2x" title="Comments"></i>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No posts yet</h2>
                <p>This blogger hasn't published anything yet.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php $statement->close(); $con->close(); ?>
</body>
</html>
