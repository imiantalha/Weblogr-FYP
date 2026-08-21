<?php

declare(strict_types=1);
require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';
$statement = $con->prepare('SELECT user_type FROM users WHERE user_id = ? LIMIT 1'); $statement->bind_param('i', $user_id); $statement->execute(); $user = $statement->get_result()->fetch_assoc(); $statement->close();
if ($user === null || $user['user_type'] !== 'Admin') { $con->close(); http_response_code(403); exit('Administrator access required.'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') { verify_csrf(); if (isset($_POST['delete_all'])) { $statement = $con->prepare('DELETE FROM reports'); $statement->execute(); $statement->close(); header('Location: reports.php'); exit; } }
$reports = $con->query('SELECT content FROM reports ORDER BY 1 DESC');
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reports | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head><body><?php include 'sidebar.php'; ?><main class="container"><h1>All Reports</h1><?php if ($reports->num_rows > 0): $count = 1; while ($row = $reports->fetch_assoc()): ?><div class="notifications"><?php echo $count . ') ' . htmlspecialchars((string) $row['content'], ENT_QUOTES, 'UTF-8'); ?></div><?php $count++; endwhile; ?><form action="reports.php" method="post"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><input type="hidden" name="delete_all" value="1"><button id="save-btn" type="submit" onclick="return confirm('Delete all reports?');">Delete All Reports</button></form><?php else: ?><div class="empty-state"><h2>No reports</h2><p>There are no reports to review.</p></div><?php endif; ?></main><?php $reports->free(); $con->close(); ?></body></html>
