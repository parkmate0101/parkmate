<?php
session_start();

$conn = new mysqli("localhost","root","","parking_system");

if($conn->connect_error){
    die("Database connection failed");
}

$uid = $_POST['uid'];
$otp = trim($_POST['otp']);

/* CHECK IF BLOCKED */

$block = $conn->query("
SELECT blocked_until 
FROM otp_verification
WHERE u_id='$uid' AND purpose='reset'
ORDER BY otp_id DESC
LIMIT 1
");

if($block->num_rows > 0){

$data = $block->fetch_assoc();

if($data['blocked_until'] != NULL && strtotime($data['blocked_until']) > time()){

header("Location: verify_reset_otp.php?error=blocked&uid=$uid");
exit();

}

}

/* VERIFY OTP (5 MIN EXPIRY) */

$sql = "
SELECT * FROM otp_verification
WHERE u_id='$uid' 
AND otp_code='$otp'
AND purpose='reset'
AND is_used=0
AND created_at >= NOW() - INTERVAL 5 MINUTE
ORDER BY otp_id DESC
LIMIT 1
";

$result = $conn->query($sql);

if($result->num_rows > 0){

/* SUCCESS */

$conn->query("
UPDATE otp_verification 
SET is_used=1 
WHERE u_id='$uid' AND otp_code='$otp'
");

$_SESSION['reset_uid'] = $uid;

header("Location: reset_password.php");
exit();

}
else{

/* INCREASE ATTEMPT COUNT */

$conn->query("
UPDATE otp_verification
SET attempt_count = attempt_count + 1
WHERE u_id='$uid'
AND purpose='reset'
ORDER BY otp_id DESC
LIMIT 1
");

/* GET ATTEMPT COUNT */

$attempt = $conn->query("
SELECT attempt_count 
FROM otp_verification
WHERE u_id='$uid'
AND purpose='reset'
ORDER BY otp_id DESC
LIMIT 1
");

$row = $attempt->fetch_assoc();

if($row['attempt_count'] >= 5){

/* BLOCK USER 15 MINUTES */

$conn->query("
UPDATE otp_verification
SET blocked_until = NOW() + INTERVAL 15 MINUTE
WHERE u_id='$uid'
AND purpose='reset'
ORDER BY otp_id DESC
LIMIT 1
");

header("Location: verify_reset_otp.php?error=blocked&uid=$uid");
exit();

}

header("Location: verify_reset_otp.php?error=invalid_otp&uid=$uid");
exit();

}
?>