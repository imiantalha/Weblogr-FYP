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

$blog_id = filter_input(INPUT_GET, 'blog_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($blog_id === false || $blog_id === null) {
    http_response_code(400);
    exit('A valid post ID is required.');
}

$user_id = (int) $_SESSION['user_id'];
$statement = $con->prepare('SELECT blog_id, title, description, category FROM blogs WHERE blog_id = ? AND user_id = ? LIMIT 1');
$statement->bind_param('ii', $blog_id, $user_id);
$statement->execute();
$row = $statement->get_result()->fetch_assoc();
$statement->close();
$con->close();

if ($row === null) {
    http_response_code(404);
    exit('Blog post not found or access denied.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Post</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
    <div class="top-bar">
        <span id="top-bar-title">Edit Post</span>
    </div>

    <?php include 'sidebar.php'; ?>

    <div class="writing-section">
        <form action="update_post.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="blog_id" value="<?php echo (int) $row['blog_id']; ?>">
            <input id="blog-title" name="title" type="text" required maxlength="255" placeholder="Blog Title..." value="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"><br>
            <input type="file" name="uploadimage" accept="image/jpeg,image/png,image/gif,image/webp"><br><br>
            <textarea id="blog-para" name="description" cols="50" rows="7" required placeholder="description..." autocomplete="off"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
            <label for="category">Category: </label>
            <select name="category" id="category" required style="width: 150px; text-align: center; font-size: 18px; color: #999;">
                <option value="">--Category--</option>
                <option value="education" <?php echo $row['category'] === 'education' ? 'selected' : ''; ?>>Education</option>
                <option value="technology" <?php echo $row['category'] === 'technology' ? 'selected' : ''; ?>>Technology</option>
                <option value="travel" <?php echo $row['category'] === 'travel' ? 'selected' : ''; ?>>Travel</option>
                <option value="food" <?php echo $row['category'] === 'food' ? 'selected' : ''; ?>>Food</option>
                <option value="fashion" <?php echo $row['category'] === 'fashion' ? 'selected' : ''; ?>>Fashion</option>
                <option value="sport" <?php echo $row['category'] === 'sport' ? 'selected' : ''; ?>>Sport</option>
                <option value="other" <?php echo $row['category'] === 'other' ? 'selected' : ''; ?>>Others</option>
            </select>
            <br><br>
            <button id="save-btn" type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
