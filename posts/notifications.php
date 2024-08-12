<?php
include '../database/db.php';

session_start();
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>
<body>

    <?php include 'sidebar.php'; ?>

        <h1>Notifications</h1>
        <?php
        if ($result->num_rows > 0) {
            // Display notifications
            $count = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<div class='notifications'>" . $count . ') ' . $row['content'] . "<br></div>";
                $count++;
            }

            ?>
            <!-- Button to delete all notifications -->
            <form action="delete_notifications.php" method="post">
                <button id="save-btn" type="submit" name="delete_all"><i class='fas fa-trash-alt fa-2x' title='Delete All'></i></button>
            </form>
        <?php
        } else {
            echo "<div style='text-align: center;'> No notification found. </div>";
        }
        $stmt->close();
        ?>
</body>
</html>
