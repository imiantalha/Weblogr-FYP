<?php
session_start();

// Redirect to profile.php if the user is already logged in
if (isset($_SESSION["username"])) {
    header("Location: profile.php");
    exit;
}

$insert = false;
$username_already_exist = false;
$email_already_exist = false;

if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {

    // Database connection
    include '../database/db.php';

    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];

    // Check if the username already exists
    $select = "SELECT username FROM users WHERE username = ? AND is_verified = 1";
    $statement = $con->prepare($select);
    $statement->bind_param("s", $username);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        $username_already_exist = true;
    }

    // Check if the email already exists
    $select = "SELECT email, is_verified FROM users WHERE email = ? AND is_verified = 1";

    $statement = $con->prepare($select);
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        $email_already_exist = true;
    }

    if ($email_already_exist) {
        echo '<script>alert("Email already exist.");</script>';

    } 
    elseif ($username_already_exist) {
      echo '<script>alert("Username already exist.");</script>';
    }
    else {

        $otp = mt_rand(100000, 999999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into database
        $sql = "INSERT INTO `users` (`fullname`,`username`, `email`, `password`, `otp`, `date`, `is_verified`)
        VALUES (?, ?, ?, ?, ?, current_timestamp(), 0)";

        $statement = $con->prepare($sql);
        $statement->bind_param("sssss", $fullname, $username, $email, $password_hash, $otp);
        $statement->execute();
        $insert = true;

        require 'vendor/autoload.php';
        $reset =false;
        // handle mail functions
        include 'mail.php';
    }
}

?>



<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIGNUP</title> 
  <script src="index.js"></script>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>

<body>
  <div class="center-items">
    <div class="container">
      <div class="welcome">
        <h1>Weblogr</h1>
      </div>
      <div class="wrapper">
        <div class="title"><span>Registration Form</span></div>

        <form onsubmit="return form_validation()" method="post">
          <div class="row">
            <i class="fas fa-user"></i>
            <input type="text" placeholder="Full Name" required name="fullname" id="fullname">
          </div>
          <div class="row">
            <i class="fas fa-envelope"></i>
            <input type="email" placeholder="Email" required name="email" id="email">
          </div>
          <div class="row">
            <i class="fas fa-user"></i>
            <input type="text" placeholder="Username" required name="username" id="username" minlength="3">
          </div>
          <div class="row">
            <i class="fas fa-lock"></i>
            <input type="password" placeholder="Password" required name="password" id="password" maxminlength="5" maxlength="10">
          </div>
          <div class="row">
            <i class="fas fa-key"></i>
            <input type="password" placeholder="Confirm password" required name="confirm_password" id="confirm_password" minlength="5" maxlength="10">
          </div>

          <div class="row button">
            <input type="submit" value="Signup" name="signup">
          </div>
          <div class="signup-link">Have an account? <a href="login.php">Login now</a></div>
        </form>
      </div>
    </div>
  </div>
  
</body>
</html>
