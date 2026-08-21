<?php

declare(strict_types=1);

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

if (!isset($_SESSION['user_id'], $_SESSION['username'])) {
    header('Location: ../registration/login.php');
    exit;
}

require '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$username = strtoupper((string) $_SESSION['username']);
$is_draft = isset($_POST['draft']);
$from_draft = filter_var($_POST['from_draft'] ?? false, FILTER_VALIDATE_BOOLEAN);
$draft_id = filter_var($_POST['draft_id'] ?? 0, FILTER_VALIDATE_INT);
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));

$allowed_categories = ['education', 'technology', 'travel', 'food', 'fashion', 'sport', 'other'];
if ($title === '' || mb_strlen($title) > 255 || $description === '' || !in_array($category, $allowed_categories, true)) {
    http_response_code(422);
    exit('Invalid post data.');
}

$filename = null;

if (isset($_FILES['uploadimage']) && $_FILES['uploadimage']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['uploadimage']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        exit('Image upload failed.');
    }

    if ((int) $_FILES['uploadimage']['size'] > 5 * 1024 * 1024) {
        http_response_code(413);
        exit('Image must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['uploadimage']['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime])) {
        http_response_code(415);
        exit('Only JPG, PNG, GIF, and WebP images are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $destination = dirname(__DIR__) . '/images/' . $filename;

    if (!move_uploaded_file($_FILES['uploadimage']['tmp_name'], $destination)) {
        http_response_code(500);
        exit('Unable to save the uploaded image.');
    }
}

try {
    $con->begin_transaction();

    if ($from_draft) {
        if (!$draft_id) {
            throw new RuntimeException('Invalid draft.');
        }

        $statement = $con->prepare('SELECT image FROM draft_posts WHERE draft_id = ? AND user_id = ? LIMIT 1');
        $statement->bind_param('ii', $draft_id, $user_id);
        $statement->execute();
        $draft = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($draft === null) {
            throw new RuntimeException('Draft not found.');
        }

        if ($filename === null) {
            $filename = $draft['image'];
        }
    }

    if ($is_draft) {
        if ($from_draft) {
            $statement = $con->prepare(
                'UPDATE draft_posts
                 SET title = ?, created_date = CURRENT_DATE(), image = ?, description = ?, category = ?
                 WHERE draft_id = ? AND user_id = ?'
            );
            $statement->bind_param('ssssii', $title, $filename, $description, $category, $draft_id, $user_id);
        } else {
            $statement = $con->prepare(
                'INSERT INTO draft_posts (title, created_date, image, description, category, user_id)
                 VALUES (?, CURRENT_DATE(), ?, ?, ?, ?)'
            );
            $statement->bind_param('ssssi', $title, $filename, $description, $category, $user_id);
        }

        $statement->execute();
        $statement->close();
        $con->commit();
        $con->close();

        header('Location: draft_posts.php');
        exit;
    }

    $statement = $con->prepare(
        'INSERT INTO blogs (title, created_date, image, description, category, user_id)
         VALUES (?, CURRENT_TIMESTAMP(), ?, ?, ?, ?)'
    );
    $statement->bind_param('ssssi', $title, $filename, $description, $category, $user_id);
    $statement->execute();
    $statement->close();

    $notification_content = "$username posted a new post.";
    $followers = $con->prepare('SELECT follower_id FROM followers WHERE blogger_id = ?');
    $followers->bind_param('i', $user_id);
    $followers->execute();
    $result = $followers->get_result();

    $notification = $con->prepare('INSERT INTO notifications (content, user_id) VALUES (?, ?)');
    while ($follower = $result->fetch_assoc()) {
        $follower_id = (int) $follower['follower_id'];
        $notification->bind_param('si', $notification_content, $follower_id);
        $notification->execute();
    }
    $notification->close();
    $followers->close();

    if ($from_draft) {
        $delete_draft = $con->prepare('DELETE FROM draft_posts WHERE draft_id = ? AND user_id = ?');
        $delete_draft->bind_param('ii', $draft_id, $user_id);
        $delete_draft->execute();
        $delete_draft->close();
    }

    $con->commit();
    $con->close();

    header('Location: index.php');
    exit;
} catch (Throwable $exception) {
    $con->rollback();
    $con->close();

    if ($filename !== null && isset($destination) && is_file($destination)) {
        unlink($destination);
    }

    error_log('Post save failed: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Unable to save the post. Please try again.';
}
