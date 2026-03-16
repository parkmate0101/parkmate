<?php

$conn = new mysqli("localhost","root","","parking_system");

if($conn->connect_error){
    die("Database connection failed");
}

$email = $_POST['email'];

/* Check email in users table */
$result = $conn->query("SELECT u_id,name FROM users WHERE email='$email'");

if($result->num_rows == 0){
    die("Email not registered");
}

$row = $result->fetch_assoc();

$uid = $row['u_id'];
$name = $row['name'];

/* Generate OTP */
$otp = rand(100000,999999);

/* Expiry time */
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

/* Disable previous OTPs */
$conn->query("UPDATE otp_verification 
              SET is_used=1 
              WHERE u_id='$uid' AND purpose='reset'");

/* Insert new OTP */
$conn->query("INSERT INTO otp_verification
              (u_id,otp_code,purpose,expires_at)
              VALUES('$uid','$otp','reset','$expiry')");

/* Send email */
require "send_otp_mail.php";
sendOTP($email,$name,$otp,"reset");

/* Redirect to OTP page */
header("Location: verify_reset_otp.php?uid=$uid");
exit();
echo "otp generated".$otp;
?>