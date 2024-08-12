<?php

  include '../database/db.php';
  if(isset($_POST['password'], $_POST['confirm_password'])) {
    $email  = "talhaarshad427@gmail.com";
    // $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password === $confirm_password) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the password for the given email address
        $sql = "UPDATE users SET password='$hashed_password' WHERE email='$email'";
        if ($con->query($sql)) {
          echo "Password updated successfully.";
          header("Location: login.php");
          
        } else {
          echo "Please try again.";
        }
    } else {
        echo "Passwords do not match.";
    }
  }

?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password </title> 
  <script src="index.js"></script>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
</head>

<body>
  <div class="container">
    <div class="welcome">
        <h1>Welcome back to Weblogr</h1>
    </div>

    <div class="wrapper">
      <div class="title"><span>Reset Password</span></div>

      <form name="reset-password" method="post" onsubmit="return form_validation()">
        <div class="row">
          <i class="fas fa-lock"></i>
          <input type="password" placeholder="Enter password" minlength="5" maxlength="8" required name="password" id="password">
        </div>
        <div class="row">
          <i class="fas fa-key"></i>
          <input type="password" placeholder="Confime Password" minlength="5" maxlength="8" required name="confirm_password" id="confirm_password">
        </div>
        <div class="row button">
          <input type="submit" value="Reset" name="reset">
        </div>
        <div class="signup-link">Don't forgot? <a href="login.php">Login</a></div>
      </form>
    </div>
  </div>
</body>
</html>