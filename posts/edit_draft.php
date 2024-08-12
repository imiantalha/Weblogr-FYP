<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: ../registration/profile.php");
    exit;
}

include '../database/db.php';
$from_draft = TRUE;

if (isset($_GET['draft_id'])) {
    $draft_id = $_GET['draft_id'];

    $sql = "SELECT * FROM draft_posts WHERE draft_id=$draft_id";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Draft</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>
    <div class="top-bar">
        <span id="top-bar-title">Edit Draft</span>
    </div>

    <?php include 'sidebar.php'; ?>
    
    <div class="writing-section">
    <form action="save_post.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="draft_id" value="<?php echo $draft_id; ?>">
        <input type="hidden" name="from_draft" value="<?php echo $from_draft; ?>">
        <input id="blog-title" name="title" type="text" placeholder="Blog Title..." value="<?php echo $row['title']; ?>" autocomplete="off"><br>
        <input type="file" name="uploadimage"><br><br>
        <textarea id="blog-para" name="description" cols="50" rows="7" placeholder="Description..." autocomplete="off"><?php echo $row['description']; ?></textarea><br><br>
        <!-- category -->
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
        <!-- checkbox for draft option -->
        <input type="checkbox" id="draft" name="draft">
        <label for="draft">Draft</label><br>
        <button id="save-btn" type="submit" name="save_draft">Upload</button>
    </form>

    </div>
</body>
</html>

<?php
    } else {
        echo "Draft post not found.";
    }
} else {
    echo "Draft ID not provided.";
}
?>
