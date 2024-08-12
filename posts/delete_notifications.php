<?php
include '../database/db.php';

session_start();
$user_id = $_SESSION['user_id'];

// Check if the delete_all button is clicked
if (isset($_POST['delete_all'])) {
    
    $sql_delete = "DELETE FROM notifications WHERE user_id = ?";
    $stmt_delete = $con->prepare($sql_delete);
    $stmt_delete->bind_param("i", $user_id);

    // Execute delete statement
    if (!$stmt_delete->execute()) {
        die('Error executing delete statement: ' . $stmt_delete->error);
    }
    $stmt_delete->close();

    header("Location: notifications.php");
    exit();
} else {
    // If button is not clicked
    header("Location: notifications.php");
    exit();
}

?>
