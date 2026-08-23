<?php
/**
 * Legacy Weblogr post URL compatibility controller.
 *
 * Older authenticated pages used /posts/blog.php?blog_id=123 while the
 * public publishing system now uses /read/{id}/{slug}. Keep the old URL
 * working so users never land on an Apache 404 page.
 */
declare(strict_types=1);

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/public_helpers.php';

$blog_id = filter_var($_GET['blog_id'] ?? 0, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if (!$blog_id) {
    public_not_found(
        'Story link is incomplete',
        'We could not identify the story you wanted to open. Please return to Discover and choose a story from there.'
    );
}

$stmt = $con->prepare('SELECT blog_id, title FROM blogs WHERE blog_id = ? AND status = ? LIMIT 1');
$status = 'published';
$stmt->bind_param('is', $blog_id, $status);
$stmt->execute();
$story = $stmt->get_result()->fetch_assoc();
$stmt->close();
$con->close();

if (!$story) {
    public_not_found(
        'Story not found',
        'This story may have been removed, unpublished, or the link may be outdated. Explore the latest stories to find something new to read.'
    );
}

header('Location: ' . article_url((int) $story['blog_id'], (string) $story['title']), true, 302);
exit;
