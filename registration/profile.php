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
    header('Location: login.php');
    exit;
}

require '../database/db.php';

$user_id = (int) $_SESSION['user_id'];

$statement = $con->prepare('SELECT full_name, bio, profile_picture FROM profile WHERE user_id = ? LIMIT 1');
$statement->bind_param('i', $user_id);
$statement->execute();
$profile_data = $statement->get_result()->fetch_assoc();
$statement->close();

$full_name = $profile_data['full_name'] ?? 'Your name';
$bio = $profile_data['bio'] ?? 'Bio';
$profile_picture = !empty($profile_data['profile_picture']) ? $profile_data['profile_picture'] : 'logo.PNG';

$statement = $con->prepare('SELECT COUNT(*) AS post_count FROM blogs WHERE user_id = ?');
$statement->bind_param('i', $user_id);
$statement->execute();
$post_count = (int) $statement->get_result()->fetch_assoc()['post_count'];
$statement->close();

$statement = $con->prepare('SELECT COUNT(*) AS follower_count FROM followers WHERE blogger_id = ?');
$statement->bind_param('i', $user_id);
$statement->execute();
$follower_count = (int) $statement->get_result()->fetch_assoc()['follower_count'];
$statement->close();

$statement = $con->prepare('SELECT COUNT(*) AS following_count FROM followers WHERE follower_id = ?');
$statement->bind_param('i', $user_id);
$statement->execute();
$following_count = (int) $statement->get_result()->fetch_assoc()['following_count'];
$statement->close();

$follower_statement = $con->prepare(
    'SELECT u.username FROM users u INNER JOIN followers f ON u.user_id = f.follower_id WHERE f.blogger_id = ? ORDER BY u.username'
);
$follower_statement->bind_param('i', $user_id);
follower_statement: null;
$follower_statement->execute();
$follower_result = $follower_statement->get_result();

$following_statement = $con->prepare(
    'SELECT u.username, u.user_id FROM users u INNER JOIN followers f ON u.user_id = f.blogger_id WHERE f.follower_id = ? ORDER BY u.username'
);
$following_statement->bind_param('i', $user_id);
$following_statement->execute();
$following_result = $following_statement->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include '../posts/sidebar.php'; ?>

    <h2 class="profile-h2">Welcome to Your Weblogr's Profile</h2>

    <div class="profile-container">
        <div class="profile-picture">
            <img src="<?php echo htmlspecialchars('../uploads/' . $profile_picture, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile Picture">
        </div>
        <div class="user-info">
            <h1><?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="stats">
                <span><strong><?php echo $post_count; ?></strong><br>Posts</span>

                <span><strong><?php echo $follower_count; ?></strong><br>
                    <select name="followers" id="followers" style="width: 100px;" class="filter">
                        <option value="">Followers</option>
                        <?php while ($row = $follower_result->fetch_assoc()): ?>
                            <?php $follower_username = strtoupper((string) $row['username']); ?>
                            <option value="<?php echo htmlspecialchars($follower_username, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($follower_username, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </span>

                <span><strong><?php echo $following_count; ?></strong><br>
                    <select name="following" id="following" style="width: 100px;" class="filter">
                        <option value="">Following</option>
                        <?php while ($row = $following_result->fetch_assoc()): ?>
                            <option value="<?php echo (int) $row['user_id']; ?>"><?php echo htmlspecialchars(strtoupper((string) $row['username']), ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </span>
            </div>
            <p><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></p>

            <a href="edit_profile.php" style="display:inline-block;"><i class="fas fa-user-edit fa-2x" title="Edit Profile"></i></a>
        </div>
    </div>

    <?php
    $follower_statement->close();
    $following_statement->close();
    $con->close();
    ?>
</body>
</html>
