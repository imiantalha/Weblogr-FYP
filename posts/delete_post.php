<?php
// Include database connection
include '../database/db.php';

// Check if the post ID is provided
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];
    
    $sql = "DELETE FROM comments WHERE blog_id = $blog_id";
    if ($con->query($sql) === TRUE) {
        // Delete the blog post from the database
        $sql = "DELETE FROM blogs WHERE blog_id= $blog_id";
        if ($con->query($sql) === TRUE) {
            
            header("Location: user_posts.php");
            exit();
        } else {
            echo "Error deleting post: " . $con->error;
        }
        } else {
        echo "Post ID not provided.";
        }
}

    // Check if the post draft_id is provided delete draft..
if (isset($_GET['draft_id'])) {
    $draft_id = $_GET['draft_id'];

        // Delete the draft post from the database
        $sql = "DELETE FROM draft_posts WHERE draft_id= $draft_id";
        if ($con->query($sql) === TRUE) {
            
            header("Location: draft_posts.php");
            exit();
        } else {
            echo "Error deleting draft: " . $con->error;
        }
    } else {
        echo "Draft ID not provided.";
}

?>
