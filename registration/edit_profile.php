<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: profile.php");
    exit;
}

include '../database/db.php';

// Retrieve user_id based on the username
$username = $_SESSION["username"];
$user_id = $_SESSION["user_id"];

// Check if profile data already exists for the user
$sql_check_profile = "SELECT * FROM profile WHERE user_id = $user_id";
$result_check_profile = $con->query($sql_check_profile);
if ($result_check_profile && $result_check_profile->num_rows > 0) {
    // Profile data exists, update the existing record
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = $_POST["full_name"];
        $bio = $_POST["bio"];

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
            $filename = $_FILES['profile_picture']['name'];
            $tempname = $_FILES['profile_picture']['tmp_name'];
            move_uploaded_file($tempname, "../uploads/" . $filename);
            $profile_picture = $filename;
        }

        // Update name in the profile table if not empty
        if (!empty($full_name)) {
            $sql_update_name = "UPDATE profile SET full_name = ? WHERE user_id = ?";
            $stmt_update_name = $con->prepare($sql_update_name);
            $stmt_update_name->bind_param("si", $full_name, $user_id);
            $stmt_update_name->execute();
            $stmt_update_name->close();
        }

        // Update bio in the profile table if not empty
        if (!empty($bio)) {
            $sql_update_bio = "UPDATE profile SET bio = ? WHERE user_id = ?";
            $stmt_update_bio = $con->prepare($sql_update_bio);
            $stmt_update_bio->bind_param("si", $bio, $user_id);
            $stmt_update_bio->execute();
            $stmt_update_bio->close();
        }

        // Update pic in the profile table if not empty
        if (isset($profile_picture)) {
            $sql_update_pic = "UPDATE profile SET profile_picture = ? WHERE user_id = ?";
            $stmt_update_pic = $con->prepare($sql_update_pic);
            $stmt_update_pic->bind_param("si", $profile_picture, $user_id);
            $stmt_update_pic->execute();
            $stmt_update_pic->close();
        }

        header("Location: profile.php");
        exit;
    }

} else {
    // Profile data does not exist, insert a new record
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = $_POST["full_name"];
        $bio = $_POST["bio"];

        // Handle profile picture upload
        if (isset($_FILES['profile_picture'])) {
            $filename = $_FILES['profile_picture']['name'];
            $tempname = $_FILES['profile_picture']['tmp_name'];
            move_uploaded_file($tempname, "../uploads/" . $filename);
        }

        // Insert data into profile table
        $sql_insert_profile = "INSERT INTO profile (user_id, full_name, bio, profile_picture) VALUES (?, ?, ?, ?)";
        $stmt_insert_profile = $con->prepare($sql_insert_profile);
        $stmt_insert_profile->bind_param("isss", $user_id, $full_name, $bio, $filename);
        if ($stmt_insert_profile->execute()) {
            header("Location: profile.php");
            exit;
        } else {
            echo "Error inserting profile: " . $stmt_insert_profile->error;
        }
        $stmt_insert_profile->close();
    }
}

$con->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../registration/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body> 

    <?php include '../posts/sidebar.php'; ?>
    
    <h1>Edit Profile</h1>
    <div class="edit">
        <div class="profile-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="full_name">Full Name:</label><br>
                <input type="text" id="full_name" name="full_name"><br><br>
                
                <label for="bio">Bio:</label><br>
                <textarea id="bio" name="bio" rows="5" cols="45"></textarea><br><br>
                
                <label for="profile_picture">Profile Picture:</label><br>
                <input type="file" id="profile_picture" name="profile_picture"><br><br>
                
                <input class="profile-btn" type="submit" value="Submit">
            </form>
        </div>
    </div>
</body>
</html>
