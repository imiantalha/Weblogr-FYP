<?php

declare(strict_types=1);

require '../includes/security.php';
$user_id = require_authentication();
require '../database/db.php';

$error = null;
$profile = ['full_name' => '', 'bio' => '', 'profile_picture' => ''];
$statement = $con->prepare('SELECT full_name, bio, profile_picture FROM profile WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $user_id); $statement->execute();
$existing = $statement->get_result()->fetch_assoc(); $statement->close();
if ($existing) $profile = $existing;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $full_name = trim((string) ($_POST['full_name'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));
    if ($full_name === '' || mb_strlen($full_name) > 100) $error = 'Please enter a valid name.';
    elseif (mb_strlen($bio) > 1000) $error = 'Bio must be 1000 characters or fewer.';
    else {
        $profile_picture = $profile['profile_picture'];
        $new_file = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK || (int) $_FILES['profile_picture']['size'] > 5 * 1024 * 1024) $error = 'Profile image must be 5 MB or smaller.';
            else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['profile_picture']['tmp_name']);
                $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (!isset($extensions[$mime])) $error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
                else { $new_file = bin2hex(random_bytes(16)) . '.' . $extensions[$mime]; $destination = dirname(__DIR__) . '/uploads/' . $new_file; if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) $error = 'Unable to save the profile image.'; else $profile_picture = $new_file; }
            }
        }
        if ($error === null) {
            if ($existing) {
                $statement = $con->prepare('UPDATE profile SET full_name = ?, bio = ?, profile_picture = ? WHERE user_id = ?');
                $statement->bind_param('sssi', $full_name, $bio, $profile_picture, $user_id);
            } else {
                $statement = $con->prepare('INSERT INTO profile (user_id, full_name, bio, profile_picture) VALUES (?, ?, ?, ?)');
                $statement->bind_param('isss', $user_id, $full_name, $bio, $profile_picture);
            }
            $statement->execute(); $statement->close(); $con->close();
            header('Location: profile.php'); exit;
        }
        if ($new_file !== null && isset($destination) && is_file($destination)) unlink($destination);
    }
}
$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
$con->close();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Edit Profile | Weblogr</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/></head><body><?php include '../posts/sidebar.php'; ?><main class="container"><h1>Edit Profile</h1><div class="profile-container"><form action="" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>"><?php if ($error): ?><p role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?><label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" maxlength="100" value="<?php echo htmlspecialchars((string) $profile['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required><br><label for="bio">Bio</label><textarea id="bio" name="bio" rows="5" maxlength="1000"><?php echo htmlspecialchars((string) $profile['bio'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><label for="profile_picture">Profile Picture</label><input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp"><br><button class="profile-btn" type="submit">Save Changes</button></form></div></main></body></html>
