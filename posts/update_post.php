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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

require '../database/db.php';

$user_id = (int) $_SESSION['user_id'];
$blog_id = filter_var($_POST['blog_id'] ?? 0, FILTER_VALIDATE_INT);
$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));
$allowed_categories = ['education', 'technology', 'travel', 'food', 'fashion', 'sport', 'other'];

if (!$blog_id || $title === '' || mb_strlen($title) > 255 || $description === '' || !in_array($category, $allowed_categories, true)) {
    $con->close();
    http_response_code(422);
    exit('Invalid post data.');
}

$filename = null;
$destination = null;

if (isset($_FILES['uploadimage']) && $_FILES['uploadimage']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['uploadimage']['error'] !== UPLOAD_ERR_OK) {
        $con->close();
        http_response_code(400);
        exit('Image upload failed.');
    }

    if ((int) $_FILES['uploadimage']['size'] > 5 * 1024 * 1024) {
        $con->close();
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
        $con->close();
        http_response_code(415);
        exit('Only JPG, PNG, GIF, and WebP images are allowed.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $destination = dirname(__DIR__) . '/images/' . $filename;

    if (!move_uploaded_file($_FILES['uploadimage']['tmp_name'], $destination)) {
        $con->close();
        http_response_code(500);
        exit('Unable to save the uploaded image.');
    }
}

try {
    $statement = $con->prepare('SELECT image FROM blogs WHERE blog_id = ? AND user_id = ? LIMIT 1');
    $statement->bind_param('ii', $blog_id, $user_id);
    $statement->execute();
    $post = $statement->get_result()->fetch_assoc();
    $statement->close();

    if ($post === null) {
        throw new RuntimeException('Post not found or access denied.');
    }

    if ($filename !== null) {
        $statement = $con->prepare(
            'UPDATE blogs SET title = ?, created_date = CURRENT_TIMESTAMP(), image = ?, description = ?, category = ? WHERE blog_id = ? AND user_id = ?'
        );
        $statement->bind_param('ssssii', $title, $filename, $description, $category, $blog_id, $user_id);
    } else {
        $statement = $con->prepare(
            'UPDATE blogs SET title = ?, created_date = CURRENT_TIMESTAMP(), description = ?, category = ? WHERE blog_id = ? AND user_id = ?'
        );
        $statement->bind_param('sssii', $title, $description, $category, $blog_id, $user_id);
    }

    $statement->execute();
    $statement->close();
    $con->close();

    header('Location: index.php');
    exit;
} catch (Throwable $exception) {
    if (is_string($destination) && is_file($destination)) {
        unlink($destination);
    }

    error_log('Post update failed: ' . $exception->getMessage());
    $con->close();
    http_response_code($exception->getMessage() === 'Post not found or access denied.' ? 404 : 500);
    echo $exception->getMessage() === 'Post not found or access denied.'
        ? 'Post not found or access denied.'
        : 'Unable to update the post.';
}
