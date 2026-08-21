<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

$draft_id = filter_var($_GET['draft_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$draft_id) {
    $con->close();
    http_response_code(400);
    exit('A valid draft ID is required.');
}

$user_id = (int) $_SESSION['user_id'];
$statement = $con->prepare('SELECT draft_id, title, image, description, category FROM draft_posts WHERE draft_id = ? AND user_id = ? LIMIT 1');
$statement->bind_param('ii', $draft_id, $user_id);
$statement->execute();
$draft = $statement->get_result()->fetch_assoc();
$statement->close();
$con->close();

if ($draft === null) {
    http_response_code(404);
    exit('Draft not found or access denied.');
}

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$csrf = e(csrf_token());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Draft | Weblogr</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="top-bar"><span id="top-bar-title">Edit Draft</span></div>
<main class="writing-section">
    <form action="save_post.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="draft_id" value="<?php echo (int) $draft['draft_id']; ?>">
        <input type="hidden" name="from_draft" value="1">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
        <input id="blog-title" name="title" type="text" maxlength="255" placeholder="Blog Title..." value="<?php echo e((string) $draft['title']); ?>" required autocomplete="off">
        <?php if (!empty($draft['image'])): ?>
            <p>Current image: <?php echo e((string) $draft['image']); ?></p>
        <?php endif; ?>
        <input type="file" name="uploadimage" accept="image/jpeg,image/png,image/gif,image/webp">
        <textarea id="blog-para" name="description" maxlength="10000" rows="10" placeholder="Description..." required autocomplete="off"><?php echo e((string) $draft['description']); ?></textarea>
        <select name="category" id="category" required>
            <option value="">-- Category --</option>
            <?php foreach (['education'=>'Education','technology'=>'Technology','travel'=>'Travel','food'=>'Food','fashion'=>'Fashion','sport'=>'Sport','other'=>'Others'] as $value => $label): ?>
                <option value="<?php echo $value; ?>" <?php echo $draft['category'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <label><input type="checkbox" name="draft" checked> Keep as draft</label>
        <button id="save-btn" type="submit" name="save_draft">Save Draft</button>
        <button id="save-btn" type="submit" name="publish">Publish</button>
    </form>
</main>
</body>
</html>
