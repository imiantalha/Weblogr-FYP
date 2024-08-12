<?php
    session_start();
    
    if (!isset($_SESSION["username"])) {
        header("Location: ../registration/profile.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Content</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <script src="index.js"></script>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="top-bar">
        <span>Manage Content</span> 
    </div>
    
    <br>
    <div class="all-posts-container">
        <?php
        include '../database/db.php';

        $username = $_SESSION["username"];
        $user_id = $_SESSION["user_id"];

        $sql = "SELECT * FROM blogs WHERE 1;";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post-container'>";
                echo "<span id='display-title'>" . $row["title"] . "</span><br>";
                echo "<div class='date-container'>";
                echo "<span>" . date('d/m/Y', strtotime($row["created_date"])) . "</span><br>";
                echo "<span>" . "Blog ID: " . $row['blog_id'] . "</span><br>";
                echo "</div>";
                echo "<img id='display-image' src='../images/" . $row["image"] . "' alt='image'><br>";
                echo "<p id='display-para'>" . $row["description"] . "</p>";
                echo "<a href='delete_post.php?blog_id=" . $row["blog_id"] . "' onclick='return confirmDelete();'><i class='fas fa-trash-alt' title='Delete'></i></a>";
                echo "</div>";                
            }
        } else {
            echo "<center><span>No Blog Posts Found</span></center>";
        }

        $con->close();
        ?>
    </div>
    <br>
    
<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this post?");
    }
</script>

</body>
</html>