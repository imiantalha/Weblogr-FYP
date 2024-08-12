<?php

include '../database/db.php';

session_start();
$username = strtoupper($_SESSION['username']);

if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];

    $sql = "UPDATE blogs SET likes = likes + 1 WHERE blog_id = $blog_id";
    if ($con->query($sql) === TRUE) {

        $sql_blogger_id = "SELECT user_id FROM blogs WHERE blog_id = ?";
        $statement_blogger_id = $con->prepare($sql_blogger_id);
        $statement_blogger_id->bind_param("i", $blog_id);
        $statement_blogger_id->execute();
        $result_blogger_id = $statement_blogger_id->get_result();
        $row_blogger_id = $result_blogger_id->fetch_assoc();
        $blogger_id = $row_blogger_id['user_id'];

        $notification_content = "$username likes your post (Blog ID: $blog_id)";
        $send_notification = "INSERT INTO notifications (content, user_id) VALUES (?, ?)";
        $stmt = $con->prepare($send_notification);
        $stmt->bind_param("si", $notification_content, $blogger_id);
        $stmt->execute();

        // Retrieve the updated like count
        header("location: ../posts/index.php");
    } else {
        // Error handling
        echo "Error updating like count: " . $con->error;
    }
} else {
    // Error handling
    echo "Post ID not provided.";
}
?>
