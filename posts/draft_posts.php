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
    <title>Draft Posts</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <script src="script.js"></script>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="top-bar">
        <span>Draft Posts</span> 
    </div>

    <br>
    <div class="all-posts-container">
        <?php
        include '../database/db.php';

        $username = $_SESSION["username"];
        $sql = "SELECT user_id FROM users WHERE username = '$username'";
        $result = $con->query($sql);
        if($result) {
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = $row['user_id'];
            }
        }

        $sql = "SELECT * FROM draft_posts WHERE user_id = $user_id;";
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post-container'>";
                echo "<span id='display-title'>" . $row["title"] . "</span><br>";
                echo "<div class='date-container'>";
                echo "<span>" . date('d/m/Y', strtotime($row["created_date"])) . "</span><br><br>";
                echo "</div>";
                if($row["image"]) {
                    echo "<img id='display-image' src='../images/" . $row["image"] . "' alt='image'><br>";
                }                
                echo "<p id='display-para'>" . $row["description"] . "</p>";
                echo "<div class='like-button'>";
                echo "<a href='edit_draft.php?draft_id=" . $row["draft_id"] . "'><i class='fas fa-edit' title='Edit'></i></a>  ";
                echo "<a href='delete_post.php?draft_id=" . $row["draft_id"] . "' style='margin-left:15px' onclick='return confirmDelete();'><i class='fas fa-trash-alt' title='Delete'></i></a>";
                echo "</div>";
                echo "</div>";                
            }            
        } else {
            echo "<center><span>No Draft Post Found</span></center>";
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