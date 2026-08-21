<?php

declare(strict_types=1);

require '../includes/security.php';
require_authentication();
require '../database/db.php';

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

$csrf = e(csrf_token());
$category = trim((string) ($_GET['category'] ?? ''));
$username_filter = trim((string) ($_GET['username'] ?? ''));
$sort = (string) ($_GET['sort'] ?? '');
$popularity = (string) ($_GET['popularity'] ?? '');
$allowed_categories = ['education', 'technology', 'travel', 'food', 'fashion', 'sport', 'other'];
$allowed_sorts = ['newest_first', 'oldest_first'];
$allowed_popularity = ['popular', 'unpopular'];

$users = $con->query('SELECT username FROM users ORDER BY username ASC');
$sql = 'SELECT b.blog_id, b.title, b.created_date, b.image, b.description, b.likes, b.user_id, u.username FROM blogs b JOIN users u ON b.user_id = u.user_id';
$conditions = [];
$params = [];
$types = '';

if (in_array($category, $allowed_categories, true)) { $conditions[] = 'b.category = ?'; $params[] = $category; $types .= 's'; }
if ($username_filter !== '') { $conditions[] = 'u.username = ?'; $params[] = $username_filter; $types .= 's'; }
if ($conditions) { $sql .= ' WHERE ' . implode(' AND ', $conditions); }
if ($popularity === 'popular') { $sql .= ' ORDER BY b.likes DESC, b.created_date DESC'; }
elseif ($popularity === 'unpopular') { $sql .= ' ORDER BY b.likes ASC, b.created_date DESC'; }
elseif ($sort === 'oldest_first') { $sql .= ' ORDER BY b.created_date ASC'; }
else { $sql .= ' ORDER BY b.created_date DESC'; }

$statement = $con->prepare($sql);
if ($params) { $statement->bind_param($types, ...$params); }
$statement->execute();
$result = $statement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Weblogr</title><script src="index.js" defer></script><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"><script src="../scripts/script.js" defer></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content"><div class="all-posts-container">
<form action="index.php" method="get" class="post-filters">
<select name="category" id="category" class="filter"><option value="">By Category</option><?php foreach ($allowed_categories as $option): ?><option value="<?php echo e($option); ?>" <?php echo $category === $option ? 'selected' : ''; ?>><?php echo e(ucfirst($option)); ?></option><?php endforeach; ?></select>
<select name="username" id="username" class="filter"><option value="">By User</option><?php while ($user = $users->fetch_assoc()): ?><option value="<?php echo e($user['username']); ?>" <?php echo $username_filter === $user['username'] ? 'selected' : ''; ?>><?php echo e(strtoupper($user['username'])); ?></option><?php endwhile; ?></select>
<select name="sort" id="sort" class="filter"><option value="">By Date</option><option value="newest_first" <?php echo $sort === 'newest_first' ? 'selected' : ''; ?>>Newest First</option><option value="oldest_first" <?php echo $sort === 'oldest_first' ? 'selected' : ''; ?>>Oldest First</option></select>
<select name="popularity" id="popularity" class="filter"><option value="">Popularity</option><option value="popular" <?php echo $popularity === 'popular' ? 'selected' : ''; ?>>Most Popular</option><option value="unpopular" <?php echo $popularity === 'unpopular' ? 'selected' : ''; ?>>Less Popular</option></select>
<button type="submit" class="submit">Apply Filter</button></form>
<?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()): ?>
<article class="post-container"><span id="display-title"><?php echo e((string) $row['title']); ?></span><div class="date-container"><span><?php echo e(date('d/m/Y', strtotime($row['created_date']))); ?></span></div>
<?php if (!empty($row['image'])): ?><img id="display-image" src="../images/<?php echo rawurlencode((string) $row['image']); ?>" alt="<?php echo e((string) $row['title']); ?>"><?php endif; ?>
<div class="report"><a href="report.php?blog_id=<?php echo (int) $row['blog_id']; ?>&blogger_id=<?php echo (int) $row['user_id']; ?>" aria-label="Report post"><i class="fas fa-exclamation-triangle fa-2x" title="Report"></i></a></div>
<p id="display-para"><a href="blog_poster.php?user_id=<?php echo (int) $row['user_id']; ?>">@<?php echo e((string) $row['username']); ?></a>: <?php echo nl2br(e((string) $row['description'])); ?></p>
<div class="like-button"><button type="button" class="icon-button" onclick="likeBlog(<?php echo (int) $row['blog_id']; ?>, '<?php echo $csrf; ?>')" aria-label="Like post"><i class="fas fa-thumbs-up fa-2x" title="Like"></i> <span id="like-count-<?php echo (int) $row['blog_id']; ?>"><?php echo (int) $row['likes']; ?></span></button><a href="../comments/comments.php?blog_id=<?php echo (int) $row['blog_id']; ?>" style="margin-left:15px" aria-label="Comments"><i class="fas fa-comment fa-2x" title="Comment"></i></a></div></article>
<?php endwhile; else: ?><div class="empty-state"><span>No Blog Posts Found</span></div><?php endif; ?>
</div></div>
<?php $statement->close(); $con->close(); ?>
</body></html>
