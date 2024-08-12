<?php 
    session_start();

    if(!isset($_SESSION['username'])) {
        header('Locaton: ../registration/login.php');
        exit;
    }
    include '../database/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weblogr</title>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <script src="../scripts/script.js"></script>
</head>
<body>

<?php include 'sidebar.php'; ?>
    
<div class="content">
    <div class="all-posts-container">
        <form action="" method="GET">
            <!-- Dropdown for filtering by category -->
            <select name="category" id="category" style="width: 155px;" class="filter">
                <option value="">--By Category--</option>
                <option value="education">Education</option>
                <option value="technology">Technology</option>
                <option value="travel">Travel</option>
                <option value="food">Food</option>
                <option value="fashion">Fashion</option>
                <option value="sport">Sports</option>
                <option value="other">Others</option>
            </select>

            <!-- Dropdown for filtering by username -->
            <select name="username" id="username" style="width: 115px;" class="filter">
                <option value="">--By User--</option>
                <?php
                $select = "SELECT username FROM users";
                $statement = $con->prepare($select);
                $statement->execute();
                $result = $statement->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $username = $row['username'];
                        echo "<option value='$username'>" . strtoupper($username) . "</option>";
                    }
                } else {
                    echo "<option value=''>No users found</option>";
                }
                ?>
            </select>

            <!-- Dropdown for sorting by date -->
            <select name="sort" id="sort" style="width: 120px;" class="filter">
                <option value="">--By Date--</option>
                <option value="newest_first">Newest First</option>
                <option value="oldest_first">Oldest First</option>
            </select>
            
            <!-- Dropdown for popularity -->
            <select name="popularity" id="popularity" style="width: 135px;" class="filter">
                <option value="">--Popularity--</option>
                <option value="popular">Most Popular</option>
                <option value="unpopular">Less Popular</option>
            </select>

            <!-- Submit button for applying filters -->
            <button type="submit" class="submit">Apply Filter</button>
        </form>

        <?php

        // Default SQL query to fetch all blog posts
        $sql = "SELECT b.blog_id, b.title, b.created_date, b.image, b.description, b.likes, b.user_id, u.username 
                FROM blogs b 
                JOIN users u ON b.user_id = u.user_id";

        // Check if a category is selected
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $selected_category = mysqli_real_escape_string($con, $_GET['category']);
            $sql .= " WHERE b.category = '$selected_category'";
        }        

        // Check if a user is selected
        if (isset($_GET['username']) && !empty($_GET['username'])) {
            $selected_user = mysqli_real_escape_string($con, $_GET['username']);
            $sql .= " AND u.username = '$selected_user'";
        }        

        // Check if a sorting option is selected
        if (isset($_GET['popularity']) && !empty($_GET['popularity'])) {
            $popularity_option = mysqli_real_escape_string($con, $_GET['popularity']);
            switch ($popularity_option) {
                case 'popular':
                    $sql .= " ORDER BY b.likes DESC, b.created_date DESC";
                    break;
                case 'unpopular':
                    $sql .= " ORDER BY b.likes ASC, b.created_date DESC";
                    break;
            }
        } elseif (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $sort_option = mysqli_real_escape_string($con, $_GET['sort']);
            switch ($sort_option) {
                case 'newest_first':
                    $sql .= " ORDER BY b.created_date DESC";
                    break;
                case 'oldest_first':
                    $sql .= " ORDER BY b.created_date ASC";
                    break;
            }
        } else {
            // Default sorting by date if no sorting option is provided
            $sql .= " ORDER BY b.created_date DESC";
        }
        
        $result = $con->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='post-container'>";
                echo "<span id='display-title'>" . $row["title"] . "</span><br>";
                echo "<div class='date-container'>";
                echo "<span>" . date('d/m/Y', strtotime($row["created_date"])) . "</span><br>"; 
                echo "</div>";
                if($row["image"]) {
                    echo "<img id='display-image' src='../images/" . $row["image"] . "' alt='image'>";
                }
                echo "<div class='report'>";
                echo "<span> <a href='report.php?blog_id={$row['blog_id']}&blogger_id={$row['user_id']}'> <i class='fas fa-exclamation-triangle fa-2x' title='Report'></i> </a></span>";
                echo "</div>";
                echo "<p id='display-para'><a href='blog_poster.php?user_id=" . $row['user_id'] . "'>@" . $row['username'] . "</a>: " . $row["description"] . "</p>";
                echo "<div class='like-button'>";
                echo '<a href="#" onclick="likeBlog(' . $row["blog_id"] . '); return false;"><i class="fas fa-thumbs-up fa-2x" title="Like ' . $row["likes"] . '"></i></a>';
                echo "<a href='../comments/comments.php?blog_id=" . $row['blog_id'] . "' style='margin-left:15px'><i class='fas fa-comment fa-2x' title='Comment'></i></a>";
                echo "</div>";
                echo "</div>";                
            }
        } else {
            echo "<center><span>No Blog Posts Found</span></center>";
        }

        $con->close();
        ?>
    </div>
    <br>
</div>

</body>
</html>
