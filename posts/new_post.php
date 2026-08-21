<?php

declare(strict_types=1);
require '../includes/security.php';
require_authentication();
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Post | Weblogr</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar"><span><b>Blog | New Post</b></span></div>
<?php include 'sidebar.php'; ?>
<main class="writing-section">
    <form action="save_post.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <input id="blog-title" name="title" type="text" maxlength="255" required placeholder="Enter Blog Title" autocomplete="off">
        <input type="file" name="uploadimage" accept="image/jpeg,image/png,image/gif,image/webp">
        <textarea id="blog-para" name="description" maxlength="10000" rows="10" required placeholder="Description..." autocomplete="off"></textarea>
        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="">-- Category --</option>
            <option value="education">Education</option>
            <option value="technology">Technology</option>
            <option value="travel">Travel</option>
            <option value="food">Food</option>
            <option value="fashion">Fashion</option>
            <option value="sport">Sport</option>
            <option value="other">Others</option>
        </select>
        <label><input type="checkbox" name="draft"> Save as draft</label>
        <button id="save-btn" type="submit">Save Post</button>
    </form>
</main>
</body>
</html>
