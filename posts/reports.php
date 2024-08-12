<?php

session_start();
include '../database/db.php';

if (isset($_POST['delete_all'])) {
    
    $sql_delete = "DELETE FROM reports WHERE 1";
    $stmt_delete = $con->prepare($sql_delete);

    // Execute delete statement
    if (!$stmt_delete->execute()) {
        die('Error executing delete statement: ' . $stmt_delete->error);
    }
    $stmt_delete->close();

    header("Location: reports.php");
    exit();
}
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>

        <h1>All Reports</h1>
        <?php
        $sql = "SELECT * FROM reports WHERE 1";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Display reports
            $count = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<div class='notifications'>" . $count . ') ' . $row['content'] . "<br></div>";
                $count++;
            }

            ?>
            <!-- Button to delete all notifications -->
            <form action="" method="post">
                <button id="save-btn" type="submit" name="delete_all">Delete All Reports</button>   
            </form>
        <?php
        } else {
            echo "<div style='text-align: center;'> No report found. </div>";
        }
        $stmt->close();
        ?>
</body>
</html>
