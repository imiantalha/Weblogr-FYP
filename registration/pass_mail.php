<?php
include('smtp/PHPMailerAutoload.php');

echo smtp_mailer($email, 'Weblogr - OTP Verification', "Your OTP for Weblogr password reset is: $otp");

function smtp_mailer($to, $subject, $message) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(); 
    $mail->isSMTP(); 
    $mail->SMTPAuth = true; 
    $mail->SMTPSecure = 'tls'; 
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587; 
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    //$mail->SMTPDebug = 2; 
    $mail->Username = "talhaarshad427@gmail.com";
    $mail->Password = "efhw bqpr uyuu hjxs";
    $mail->setFrom("talhaarshad427@gmail.com", "Weblogr");
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->addAddress($to);
    $mail->SMTPOptions = array('ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => false
    ));
    
    $reset = TRUE;
    
    if ($mail->send()) {
        header("Location: otp_verification.php?email=$to&reset=$reset");
        exit;
    } else {
        // Handle email sending failure
        echo "Failed to send OTP. Please try again. Error: " . $mail->ErrorInfo;
    }
}
?>
