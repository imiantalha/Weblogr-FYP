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

$statement = $con->prepare('SELECT user_type FROM users WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $user_id);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();
$statement->close();

if ($user === null || $user['user_type'] !== 'Admin') {
    $con->close();
    http_response_code(403);
    exit('Administrator access required.');
}

$posts = $con->query('SELECT blog_id, title, created_date, image, description FROM blogs ORDER BY created_date DESC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="top-bar">
        <span>Manage Content</span>
    </div>

    <br>
    <div class="all-posts-container">
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($row = $posts->fetch_assoc()): ?>
                <div class="post-container">
                    <span id="display-title"><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></span><br>
                    <div class="date-container">
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $row['created_date'])), ENT_QUOTES, 'UTF-8'); ?></span><br>
                        <span>Blog ID: <?php echo (int) $row['blog_id']; ?></span><br>
                    </div>
                    <?php if (!empty($row['image'])): ?>
                        <img id="display-image" src="../images/<?php echo htmlspecialchars((string) $row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Post image"><br>
                    <?php endif; ?>
                    <p id="display-para"><?php echo htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="delete_post.php?blog_id=<?php echo (int) $row['blog_id']; ?>" onclick="return confirmDelete();"><i class="fas fa-trash-alt" title="Delete"></i></a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <center><span>No Blog Posts Found</span></center>
        <?php endif; ?>
    </div>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this post?');
}
</script>

</body>
</html>

<?php
$posts->free();
$con->close();
?>
