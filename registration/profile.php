<?php
    session_start();
    
    if (!isset($_SESSION["username"])) {
        header("Location: login.php");
        exit;
    }

    include '../database/db.php';

    $username = $_SESSION["username"];
    $user_id = $_SESSION["user_id"];

    // Fetch user's profile information from the profile table
    $sql_profile = "SELECT * FROM profile WHERE user_id = $user_id";
    $result_profile = $con->query($sql_profile);
    if ($result_profile && $result_profile->num_rows > 0) {
        $profile_data = $result_profile->fetch_assoc();
        $full_name = $profile_data['full_name'];
        $bio = $profile_data['bio'];
        $profile_picture = $profile_data['profile_picture'];
        if(empty($profile_picture)) {
            $profile_picture = 'logo.PNG';
        }
    } else {
        $full_name = 'Your name';
        $bio = 'Bio';
        $profile_picture = 'logo.PNG';
    }

    // Count the number of posts for the user
    $sql_post_count = "SELECT COUNT(*) AS post_count FROM blogs WHERE user_id = $user_id";
    $result_post_count = $con->query($sql_post_count);
    $post_count = ($result_post_count && $result_post_count->num_rows > 0) ? $result_post_count->fetch_assoc()["post_count"] : 0;

    $sql_follower_count = "SELECT COUNT(*) AS follower_count FROM followers WHERE blogger_id = $user_id";
    $result_follower_count = $con->query($sql_follower_count);
    $follower_count = ($result_follower_count && $result_follower_count->num_rows > 0) ? $result_follower_count->fetch_assoc()["follower_count"] : 0;

    $sql_following_count = "SELECT COUNT(*) AS following_count FROM followers WHERE follower_id = $user_id";
    $result_following_count = $con->query($sql_following_count);
    $following_count = ($result_following_count && $result_following_count->num_rows > 0) ? $result_following_count->fetch_assoc()["following_count"] : 0;

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
            <img src="<?php echo "../uploads/" . $profile_picture ?>" alt="Profile Picture">
        </div>
        <div class="user-info">
            <h1><?php echo $full_name ?></h1>
            <div class="stats">
                <span><strong><?php echo $post_count ?></strong><br>Posts</span>

                <span><strong><?php echo $follower_count ?></strong><br>
                    <select name="followers" id="followers" style="width: 100px;" class="filter">
                        <option value="">Followers</option>
                        <?php
                        $select = "SELECT u.username FROM users u 
                                INNER JOIN followers f ON u.user_id = f.follower_id 
                                WHERE f.blogger_id = ?";
                        $statement = $con->prepare($select);
                        $statement->bind_param("i", $_SESSION['user_id']);
                        $statement->execute();
                        $result = $statement->get_result();
                                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $username = strtoupper($row['username']);
                                echo "<option value='$username'>$username</option>";
                            }
                        } else {
                            echo "<option value=''>No users found</option>";
                        }
                        ?>
                    </select>
                </span>
                <span><strong><?php echo $following_count ?></strong><br>  
                <select name="following" id="following" style="width: 100px;" class="filter">
                    <option value="">Following</option>
                    <?php
                    $select = "SELECT u.username, u.user_id FROM users u 
                            INNER JOIN followers f ON u.user_id = f.follower_id 
                            WHERE f.follower_id = ?";
                    $statement = $con->prepare($select);
                    $statement->bind_param("i", $_SESSION['user_id']);
                    $statement->execute();
                    $result = $statement->get_result();
                                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $username = $row['username'];
                            $user_id = $row['user_id'];
                            echo "<option value='$user_id'>" . strtoupper($username) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No users found</option>";
                    }
                    ?>
                </select>
 
                </span>
            </div>
            <p><?php echo $bio;?></p>

            <a href="edit_profile.php" style="display:inline-block;"><i class="fas fa-user-edit fa-2x" title="Edit Profile"></i></a>
        </div>
    </div>    
</body>
</html>
