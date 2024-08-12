<?php

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit(); 
}

include '../database/db.php'; 

$follower_id = $_SESSION['user_id'];
$username = strtoupper($_SESSION['username']);

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Check if the follower already exists
    $check_follow = "SELECT * FROM followers WHERE blogger_id = ? AND follower_id = ?";
    $stmt_check_follow = $con->prepare($check_follow);
    $stmt_check_follow->bind_param("ii", $user_id, $follower_id);
    $stmt_check_follow->execute();
    $result_check_follow = $stmt_check_follow->get_result();

    if ($result_check_follow->num_rows > 0) {
        // Follower already exists
        header("Location: blog_poster.php?user_id=" . $user_id);
        exit(); 
    } else {
        // Follower does not exist, proceed with inserting the follow relationship
        $insert_follow = "INSERT INTO followers (blogger_id, follower_id) VALUES (?, ?)";
        $stmt_follow = $con->prepare($insert_follow); 
        $stmt_follow->bind_param("ii", $user_id, $follower_id);
        
        if ($stmt_follow->execute()) { 
            // Insert notification
            $notification_content = "$username started following you.";
            $insert_notification = "INSERT INTO notifications (content, user_id) VALUES (?, ?)";
            $stmt_notification = $con->prepare($insert_notification);
            $stmt_notification->bind_param("si", $notification_content, $user_id);

            if ($stmt_notification->execute()) {
                if ($stmt_notification->affected_rows > 0) {
                    header("Location: blog_poster.php?user_id=" . $user_id);
                    exit(); 
                } 
            } else {
                echo "Error executing notification insertion query.";
            }
        } else {
            echo "Error executing follow insertion query.";
        }
    }
}

?>
