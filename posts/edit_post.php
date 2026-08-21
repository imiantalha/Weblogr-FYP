<?php

declare(strict_types=1);
require '../includes/security.php';
require_authentication();
require '../database/db.php';
$blog_id = filter_input(INPUT_GET, 'blog_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$blog_id) { $con->close(); http_response_code(400); exit('A valid post ID is required.'); }
$user_id = (int) $_SESSION['user_id'];
$statement = $con->prepare('SELECT blog_id, title, description, category, image FROM blogs WHERE blog_id = ? AND user_id = ? LIMIT 1'); $statement->bind_param('ii', $blog_id, $user_id); $statement->execute(); $row = $statement->get_result()->fetch_assoc(); $statement->close(); $con->close();
if ($row === null) { http_response_code(404); exit('Blog post not found or access denied.'); }
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Edit Post | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head><body><div class="top-bar"><span>Edit Post</span></div><?php include 'sidebar.php'; ?><main class="writing-section"><form action="update_post.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="blog_id" value="<?php echo (int) $row['blog_id']; ?>"><input id="blog-title" name="title" type="text" required maxlength="255" placeholder="Blog Title..." value="<?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?>"><br><input type="file" name="uploadimage" accept="image/jpeg,image/png,image/gif,image/webp"><br><br><textarea id="blog-para" name="description" cols="50" rows="7" required placeholder="Description..."><?php echo htmlspecialchars((string) $row['description'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><label for="category">Category:</label><select name="category" id="category" required><?php foreach (['education'=>'Education','technology'=>'Technology','travel'=>'Travel','food'=>'Food','fashion'=>'Fashion','sport'=>'Sport','other'=>'Others'] as $value => $label): ?><option value="<?php echo $value; ?>" <?php echo $row['category'] === $value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php endforeach; ?></select><br><br><button id="save-btn" type="submit">Save Changes</button></form></main></body></html>
