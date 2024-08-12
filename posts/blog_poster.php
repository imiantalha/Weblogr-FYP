<?php
session_start();
$username = strtoupper($_SESSION['username']);
$user_id = $_GET['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Poster Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
    
    <?php include 'sidebar.php'; ?>

    <div class="all-posts-container">
    <h1>Welcome <?php echo strtoupper($username); ?></h1>

    <?php
        include '../database/db.php';

        $sql = "SELECT * FROM blogs WHERE user_id = $user_id ORDER BY created_date DESC";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<button id='save-btn'><a href='follow.php?user_id=" . $row['user_id'] . "'>Follow Me</a></button><br>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post-container'>";
                echo "<span id='display-title'>" . $row["title"] . "</span><br>";
                echo "<div class='date-container'>";
                echo "<span>" . date('d/m/Y', strtotime($row["created_date"])) . "</span><br><br>";
                echo "</div>";
                if($row['image']) {
                    echo "<img id='display-image' src='../images/" . $row["image"] . "' alt='image'><br>";
                }                
                echo "<p id='display-para'>" . $row["description"] . "</p>";
                // Edit and delete options
                echo "<div class='like-button'>";
                echo "<a href='../comments/likes.php?blog_id=" . $row['blog_id'] . "'><i class='fas fa-thumbs-up fa-2x' title='Like'></i></a>";
                echo "<a href='../comments/comments.php?blog_id=" . $row['blog_id'] . "' style='margin-left:15px'><i class='fas fa-comment fa-2x' title='Comment'></i></a>";
                echo "</div>";
                echo "</div>";                
            }
        }
        $con->close();
        ?>
    </div>
</body>
</html>