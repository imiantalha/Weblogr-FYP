<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <title>Comments</title>
</head>
<body>
    <div class="container">
        <a href="../posts/index.php"><b>Back</b></a><br>
        <?php

        include '../database/db.php';

        session_start();
        $user_id = $_SESSION['user_id'];
        // Check if blog_id is provided in the URL
        if (isset($_GET['blog_id'])) {
            $blog_id = $_GET['blog_id'];
            // Retrieve the blog post based on the provided blog_id
            $sql_blog = "SELECT * FROM blogs WHERE blog_id = $blog_id";
            $result_blog = $con->query($sql_blog);                
            if ($result_blog->num_rows > 0) {
                // Display the blog post content
                $row = $result_blog->fetch_assoc();
                
                echo "<div class='post-container'>";
                echo "<span id='display-title'>" . $row["title"] . "</span><br>";
                echo "<div class='date-container'>";
                echo "<span>" . date('d/m/Y', strtotime($row["created_date"])) . "</span><br><br>";
                echo "</div>";
                echo "<img id='display-image' src='../images/" . $row["image"] . "' alt='image'><br>";
                echo "<p id='display-para'>" . $row["description"] . "</p>";
                echo "</div>";
                // echo "Likes: " . $row['likes'];

                // Comment form
                echo "<form action='save_comment.php' onsubmit='return form_validation()' method='post'>";
                echo "<input type='hidden' name='blog_id' value='" . $blog_id . "'>";
                echo "<input type='hidden' name='commenter_id' value='" . $user_id . "'>";
                echo "<textarea id='blog-para' name='comment_text' cols='40' rows='2' required placeholder='Comment...'></textarea><br>";
                echo "<button id='save-btn' type='submit'>Save Comment</button>";
                echo "</form>";

                // Display existing comments for the blog post
                $sql_comments = "SELECT * FROM comments WHERE blog_id = $blog_id";
                $result_comments = $con->query($sql_comments);

                if ($result_comments->num_rows > 0) {
                    echo "<h3>Comments:</h3>";
                    while ($comment = $result_comments->fetch_assoc()) {
                        echo "<div>";
                        echo "<p style='display: inline;'>" . $comment["comment_text"] . "</p>";
                
                        // Like 
                        echo "<form action='like_a_comment.php' method='post' style='display: inline; margin-left: 10px;'>";
                        echo "<input type='hidden' name='comment_id' value='" . $comment["comment_id"] . "'>";
                        echo "<input type='hidden' name='blog_id' value='" . $comment["blog_id"] . "'>";
                        echo "<button class='like-btn' type='submit' style='color: blue;' title='Like'>";
                        echo "<i class='fas fa-thumbs-up'></i></button>";
                        echo "</form>";
                
                        echo "</div>";
                    }
                } else {
                    echo "<p>No comments yet.</p>";
                }
                
                
            } else {
                echo "<p>Blog ID not provided.</p>";
            }
        }

        $con->close();
        ?>
    </div>

    <script>
    function form_validation() {
        let text = document.getElementsByName('comment_text').value.trim();
        if (test === "") {
            alert('Comment should not be empty.');
            return false;
        }
    }
    </script>
</body>
</html>
