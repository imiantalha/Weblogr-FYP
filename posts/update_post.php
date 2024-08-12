<?php

include '../database/db.php';

// Check if the form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['blog_id'], $_POST['title'], $_POST['description'])) {
        // Sanitize input data
        $blog_id = mysqli_real_escape_string($con, $_POST['blog_id']);
        $title = mysqli_real_escape_string($con, $_POST['title']);
        $description = mysqli_real_escape_string($con, $_POST['description']);
        $category = mysqli_real_escape_string($con, $_POST['category']);

        // Check if a new image is uploaded
        if (isset($_FILES['uploadimage']) && $_FILES['uploadimage']['error'] === UPLOAD_ERR_OK) {
            $filename = $_FILES['uploadimage']['name'];
            $tempname = $_FILES['uploadimage']['tmp_name'];
            move_uploaded_file($tempname, "../images/" . $filename);

            // Update the blog post with the new image path
            $sql = "UPDATE `blogs` SET `title`='$title', `created_date`= NOW(), `image`='$filename', `description`='$description', `category`='$category' WHERE `blog_id`='$blog_id'";
        } else {
            // Update the blog post without changing the image path
            $sql = "UPDATE `blogs` SET `title`='$title', `created_date`= NOW(), `description`='$description', `category`='$category' WHERE `blog_id`='$blog_id'";
        }

        if ($con->query($sql) === TRUE) {
            // Redirect back to index.php after successful update
            header("Location: index.php");
            exit();
        } else {
            echo "Error updating post: " . $con->error;
        }
    } else {
        echo "All fields are required.";
    }
} else {
    echo "Invalid request method.";
}
?>
