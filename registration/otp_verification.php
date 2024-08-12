<?php

$otp_match = false;

if (isset($_GET['email']) && isset($_GET['reset'])) {
    $email = $_GET['email'];
    $reset = $_GET['reset'];

    if (isset($_POST['otp1']) && isset($_POST['otp2']) && isset($_POST['otp3']) &&
        isset($_POST['otp4']) && isset($_POST['otp5']) && isset($_POST['otp6'])) {

        // Validate OTP inputs
        $digit1 = $_POST['otp1'];
        $digit2 = $_POST['otp2'];
        $digit3 = $_POST['otp3'];
        $digit4 = $_POST['otp4'];
        $digit5 = $_POST['otp5']; 
        $digit6 = $_POST['otp6'];

        $entered_otp = $digit1 . $digit2 . $digit3 . $digit4 . $digit5 . $digit6;

        // Database connection
        include '../database/db.php';

        // Retrieve stored OTP
        $select = ($reset == TRUE) ? "SELECT otp FROM users WHERE email = ? AND is_verified = 1" :
                                        "SELECT otp FROM users WHERE email = ? AND is_verified = 0";
        
        $statement = $con->prepare($select);
        $statement->bind_param("s", $email);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_otp = $row['otp'];

            if ($entered_otp == $stored_otp) {

                $delete_otp = "UPDATE users SET otp = NULL WHERE email = ?";
                $delete_otp_statement = $con->prepare($delete_otp);
                $delete_otp_statement->bind_param("s", $email);
                $delete_otp_statement->execute();

                if ($reset == TRUE) {                    
                    header("Location: reset_password.php");
                    exit;
                }
                // Mark the user as verified in the database
                $update = "UPDATE users SET is_verified = 1 WHERE email = ?";
                $update_statement = $con->prepare($update);
                $update_statement->bind_param("s", $email);
                $update_statement->execute();

                $delete_otp_statement->execute();

                $otp_match = true;
                header("Location: success.php");
                exit;  
                
            } else {
                // Set OTP match flag to false
                $otp_match = false;
                $error_message = "Invalid OTP. Please try again.";
            }
        } else {
            $error_message = "No user found with provided email.";
        }
    }
} else { 
    $error_message = "Email or reset flag not provided for OTP verification. Please try again.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - Weblogr</title>
    <script src="index.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="otp-container">
        <h1>OTP Verification</h1>
        <h6>Please check your email, we've sent an OTP</h6>

        <?php if (isset($error_message)) { ?>
            <script> alert("<?php echo $error_message; ?>"); </script>
        <?php } ?>

        <form method="post">
            <div class="input-container">
                <input type="number" name="otp1" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
                <input type="number" name="otp2" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
                <input type="number" name="otp3" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
                <input type="number" name="otp4" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
                <input type="number" name="otp5" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
                <input type="number" name="otp6" class="otp-digit" min="0" max="9" maxlength="1" required oninput="manageFocus(this)">
            </div>

            <button type="submit" class="otp-button">Verify</button>
            <br>
            
        </form>
        <a href="signup.php" class="resend-otp">Resend OTP</a>
    </div>

</body>
</html>
