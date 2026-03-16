<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

function sendOTP($email,$name,$otp,$purpose){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
$mail->Host = "smtp-relay.brevo.com";
$mail->SMTPAuth = true;
$mail->Username = "9e7b9e001@smtp-brevo.com";
$mail->Password = "bskkjOr6RkL23sy";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

/* Fix SSL issue in XAMPP */
$mail->SMTPOptions = [
'ssl'=>[
'verify_peer'=>false,
'verify_peer_name'=>false,
'allow_self_signed'=>true
]
];

/* Sender (must match Brevo account) */
$mail->setFrom("parkmate0101@gmail.com","ParkMate");

/* Receiver */
$mail->addAddress($email,$name);

$mail->Subject = "Password Reset OTP";

$mail->Body = "Hello $name,

Your OTP for resetting password is: 
    <h1 style='letter-spacing:3px;'>$otp</h1>
    <p>This OTP is valid for <b>5 minutes</b>.</p>
ParkMate";

/* Send Mail */
$mail->send();

}catch(Exception $e){

echo "Mailer Error: ".$mail->ErrorInfo;

}

}
?>