<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Blog</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <div class="top-bar">
        <span><b>Blog | New Post</b></span>
    </div>
    
    <?php 
    session_start();
    include 'sidebar.php'; ?>

    <div class="all-posts-container">
        <form action="save_post.php" method="POST" enctype="multipart/form-data">
            <input id="blog-title" name="title" type="text" required placeholder="Enter Blog Title"><br>
            <input type="file" name="uploadimage"><br><br>
            <textarea id="blog-para" name="description" cols="40" rows="7" required placeholder="Description..."></textarea><br>
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
            <br>
            <!-- checkbox for draft option -->
            <input type="checkbox" id="draft" name="draft">
            <label for="draft">Draft</label><br>
            <button id="save-btn" type="submit">Upload Post</button>
        </form>
    </div>
    
</body>
</html>
