<?php

declare(strict_types=1);
require '../includes/security.php';
require_authentication();
require '../database/db.php';

$user_id = (int) $_SESSION['user_id'];
$statement = $con->prepare('SELECT b.blog_id,b.title,b.created_date,b.image,b.description,b.likes,b.user_id,u.username FROM blogs b JOIN followers f ON f.blogger_id=b.user_id JOIN users u ON u.user_id=b.user_id WHERE f.follower_id=? ORDER BY b.created_date DESC');
$statement->bind_param('i',$user_id); $statement->execute(); $result=$statement->get_result();
function e(string $value): string{return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Following | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"></head><body><?php include 'sidebar.php'; ?><main class="content"><div class="all-posts-container"><header class="feed-header"><p class="eyebrow">YOUR NETWORK</p><h1>Following</h1><p>Latest stories from bloggers you follow.</p></header><?php if($result->num_rows): while($row=$result->fetch_assoc()): ?><article class="post-container"><span id="display-title"><?php echo e((string)$row['title']); ?></span><div class="post-meta"><a href="blog_poster.php?user_id=<?php echo (int)$row['user_id']; ?>">@<?php echo e((string)$row['username']); ?></a><span><?php echo e(date('d M Y',strtotime((string)$row['created_date']))); ?></span></div><?php if(!empty($row['image'])): ?><img id="display-image" src="../images/<?php echo rawurlencode((string)$row['image']); ?>" alt="<?php echo e((string)$row['title']); ?>" loading="lazy"><?php endif; ?><p id="display-para"><?php echo nl2br(e((string)$row['description'])); ?></p><div class="post-actions"><span><i class="fas fa-heart"></i> <?php echo (int)$row['likes']; ?></span><a href="../comments/comments.php?blog_id=<?php echo (int)$row['blog_id']; ?>"><i class="far fa-comment"></i> Discuss</a></div></article><?php endwhile; else: ?><div class="empty-state"><div class="empty-icon"><i class="fas fa-user-plus"></i></div><h2>Your feed is waiting</h2><p>Follow some bloggers to see their latest stories here.</p><a href="index.php" class="submit">Discover bloggers</a></div><?php endif; ?></div></main><?php $statement->close();$con->close(); ?></body></html>
