<?php
// Include database connection
include '../database/db.php';

session_start();
$username = strtoupper($_SESSION["username"]);

// Check if the form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are filled
    if (isset($_POST['blog_id'], $_POST['comment_text'])) {
        // Sanitize input data
        $blog_id = mysqli_real_escape_string($con, $_POST['blog_id']);
        $commenter_id = mysqli_real_escape_string($con, $_POST['commenter_id']);
        $comment_text = mysqli_real_escape_string($con, $_POST['comment_text']);
        $comment_text = trim($comment_text);

        //user_id of blog poster
        $select_user_id = "SELECT user_id FROM blogs WHERE blog_id = ?";
        $select_stmt = $con->prepare($select_user_id); 
        $select_stmt->bind_param("i", $blog_id); 
        $select_stmt->execute(); 
        $result = $select_stmt->get_result(); 
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Check if the comment text is not empty
        if (!empty($comment_text)) {
            // Insert the comment into the database
            $save_comment = "INSERT INTO comments (blog_id, commenter_id, comment_text, comment_date) VALUES ('$blog_id', '$commenter_id', '$comment_text', NOW())";
            if ($con->query($save_comment)) {
                // Comment inserted successfully

                $notification_content = "$username commented on your post (Id: $blog_id) <br> '$comment_text'";
                $send_notification = "INSERT INTO notifications (content, user_id) VALUES (?, ?)";
                $stmt = $con->prepare($send_notification);
                $stmt->bind_param("si", $notification_content, $user_id);
                $stmt->execute();
                if(($stmt->affected_rows > 0)) {
                    //on success
                    header("Location: comments.php?blog_id=" . $blog_id);
                    exit(); 
                } else {
                    // Error inserting notification
                    echo "Error: " . $send_notification . "<br>" . $con->error;
                }    
            } else {
                // Error inserting comment
                echo "Error: " . $save_comment . "<br>" . $con->error;
            }
        } else {
            // Comment text is empty, do not save it
            echo "<script>alert('Comment text is empty. Please enter a comment.')</script>";
            header("Location: comments.php?blog_id=" . $blog_id);
        }
    } else {
        echo "Invalid request method.";
    }
}
?>
