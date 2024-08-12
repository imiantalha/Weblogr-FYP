<?php
include '../database/db.php';

session_start();
$reporter_id = $_SESSION['user_id'];
$username = strtoupper($_SESSION['username']);

if(isset($_POST['content'])) {
    // Sanitize user input
    $content = trim($_POST['content']);
    $blog_id = $_GET['blog_id'];
    $blogger_id = $_GET['blogger_id'];
    $content .= " Blog ID: " . $blog_id;

    if(!empty($content)) {
        // Prepare and execute the INSERT statement
        $insert = "INSERT INTO reports (`blog_id`, `blogger_id`, `reporter_id`, `content`) VALUES (?, ?, ?, ?)";
        $stmt_report = $con->prepare($insert);
        $stmt_report->bind_param("iiis", $blog_id, $blogger_id, $reporter_id, $content);

        if ($stmt_report->execute()) {
            // Report insertion successful
            // Notification to blogger
            $report_content = "Someone reported your post (Id: $blog_id) <br> '$content'";
            $send_notification = "INSERT INTO notifications (content, user_id) VALUES (?, ?)";
            $stmt_notification = $con->prepare($send_notification);
            $stmt_notification->bind_param("si", $report_content, $blogger_id);
            
            if ($stmt_notification->execute()) {
                // Notification sending successful
                header("Location: index.php");
                exit();
            } else {
                // Error sending notification to blogger
                echo "Error: Unable to send notification to blogger.";
            }
        } else {
            // Error inserting report
            echo "Error: Unable to submit report.";
        }
    } else {
        header("Location: index.php");
                exit();
    } 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report a Post</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <h1>Report Inappropriate Content</h1>
    <form action='' method='post'>
        <textarea id="blog-para" name='content' cols='60' rows='5' required placeholder='Enter Your report..'></textarea><br>
        <button id='save-btn' type='submit'>Submit Report</button>
    </form>
</body>
</html>
