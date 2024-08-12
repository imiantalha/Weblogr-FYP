<?php
include '../database/db.php';

session_start();
$username = strtoupper($_SESSION['username']);

if (isset($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $blog_id = $_POST['blog_id'];
    
    // Update the like count for the comment in the database
    $sql_update_likes = "UPDATE comments SET likes = likes + 1 WHERE comment_id = ?";
    $statement = $con->prepare($sql_update_likes);
    $statement->bind_param("i", $comment_id);
    $result_update_likes = $statement->execute();
    
    if ($result_update_likes) {
        // Retrieve commenter information
        $sql_commenter_info = "SELECT commenter_id, comment_text FROM comments WHERE comment_id = ?";
        $statement_commenter_info = $con->prepare($sql_commenter_info);
        $statement_commenter_info->bind_param("i", $comment_id);
        $statement_commenter_info->execute();
        $result_commenter_info = $statement_commenter_info->get_result();
        $row_commenter_info = $result_commenter_info->fetch_assoc();
        $commenter_id = $row_commenter_info['commenter_id'];
        $comment_text = $row_commenter_info['comment_text'];
        
        $notification_content = "$username likes your comment <br> '$comment_text'";
        $send_notification = "INSERT INTO notifications (content, user_id) VALUES (?, ?)";
        $stmt = $con->prepare($send_notification);
        $stmt->bind_param("si", $notification_content, $commenter_id);
        $stmt->execute();
        
        // Redirect back to the page where the comment was liked
        header("Location: comments.php?blog_id=" . $blog_id);
        exit();
    } else {
        echo "Error updating like count.";
    }
} else {
    echo "Comment ID not provided.";
}
?>
