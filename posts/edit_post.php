<?php
    session_start();
    
    if (!isset($_SESSION["username"])) {
        header("Location: ../registration/profile.php");
        exit;
    }

    // Include database connection
    include '../database/db.php';

    // Check if the post ID is provided
    if (isset($_GET['blog_id'])) {
        $blog_id = $_GET['blog_id'];
        
    // Retrieve the blog post data from the database
    $sql = "SELECT * FROM blogs WHERE blog_id=$blog_id";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Display the form for editing the blog post
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    </head>
    <body>
        <div class="top-bar">
            <span id="top-bar-title">Edit Post</span>
        </div>

        <?php include 'sidebar.php'; ?>
        
        <div class="writing-section">
        <form action="update_post.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
            <input id="blog-title" name="title" type="text" placeholder="Blog Title..." value="<?php echo $row['title']; ?>" autocomplete="off"><br>
            <input type="file" name="uploadimage"><br><br>
            <textarea id="blog-para" name="description" cols="50" rows="7" placeholder="description..." autocomplete="off"><?php echo $row['description']; ?></textarea><br><br>
            <label for="category">Category: </label>
            <select name="category" id="category" required style="width: 150px; text-align: center; font-size: 18px; color: #999;">
                <option value="">--Category--</option>
                <option value="education">Education</option>
                <option value="technology">Technology</option>
                <option value="travel">Travel</option>
                <option value="food">Food</option>
                <option value="fashion">Fashion</option>
                <option value="sport">Sport</option>
                <option value="other">Others</option>
            </select>
            <br><br>
            <button id="save-btn" type="submit">Save Changes</button>
        </form>
        </div>
    </body>
    </html>

<?php
    } else {
        echo "Blog post not found.";
    }
} else {
    echo "Post ID not provided.";
}
?>
