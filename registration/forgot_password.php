<?php

include '../database/db.php';

$not_registered = false;

if(isset($_POST['email'])) {
    $email = $_POST['email'];

    $select = "SELECT email, is_verified FROM users WHERE email = ?";
    $statement = $con->prepare($select);
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        while( $row = $result->fetch_assoc() ) {
            if($row['is_verified'] == 1) {
                $otp = mt_rand(100000, 999999);

                $sql = "UPDATE users SET `otp` = ? WHERE email = ?";
                $statement = $con->prepare($sql);
                $statement->bind_param("ss", $otp, $email);
                $statement->execute();

                require 'vendor/autoload.php';
                // handle mail functions
                $reset = TRUE;
                include 'pass_mail.php';

                echo "<script>alert('OTP sent to your email.');</script>";
            } else {
                $not_registered = true;
            }
        }
        
        
    } 
} else {
    echo "<center>Email not provided.</center>";
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password </title> 
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>

<body>
    <div class="container">

        <div class="wrapper">
            <div class="title"> Registered Email </div>
            <form name="forgot-password" method="post">
                <div class="row">
                    <i class="fas fa-envelope"></i>
                    <input type="email" placeholder="Email" required name="email" id="email">
                </div>
                <div class="row button">
                    <input type="submit" value="Reset" name="reset">
                </div>
                <div class="signup-link">Don't forgot? <a href="login.php">Login</a></div>
            </form>
        </div>
        <br>
        <?php
            if($not_registered) {
                echo "<center>Please enter a registered email address.</center>";
            }
        ?>
    </div>
</body>
</html>
