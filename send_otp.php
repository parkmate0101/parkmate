<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include "db_connect.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mailConfig = require 'mail_config.php';
$conn->query("DELETE FROM otp_verification WHERE expires_at < NOW()");

/*|-----| Determine OTP purpose|-----*/
$type = $_GET['type'] ?? 'register';

if ($type === 'register') {
    if (!isset($_SESSION['signup_uid'], $_SESSION['signup_email'])) {
        die("Invalid request");
    }
    $uid   = $_SESSION['signup_uid'];
    $email = $_SESSION['signup_email'];

} elseif ($type === 'login') {
    if (!isset($_SESSION['login_uid'], $_SESSION['login_email'])) {
        die("Invalid request");
    }
    $uid   = $_SESSION['login_uid'];
    $email = $_SESSION['login_email'];

} else {
    die("Invalid OTP type");
}
//Resend OTP unlimited times.//
    $stmt = $conn->prepare("
    SELECT created_at FROM otp_verification 
    WHERE u_id=? AND purpose=? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->bind_param("is", $uid, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (time() - strtotime($row['created_at']) < 30) {
    $_SESSION['error'] = "Please wait 30 seconds before requesting a new OTP";
    header("Location: verify_otp_html.php?type=$type");
    exit;
}
}

/* |-----| Generate OTP |------*/
$otp    = random_int(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

/*|----| Remove old OTPs for this user + purpose|-----*/
$stmt = $conn->prepare("
    DELETE FROM otp_verification 
    WHERE u_id=? AND purpose=?
");
$stmt->bind_param("is", $uid, $type);
$stmt->execute();

/* |----- | Insert new OTP |-----*/
$stmt = $conn->prepare("
    INSERT INTO otp_verification (u_id, otp_code, purpose, expires_at)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("isss", $uid, $otp, $type, $expiry);
$stmt->execute();

/* |------ | Email (will be replaced by PHPMailer) |---- */

/* |------ | Send OTP Email |---- */
try {
    $mail = new PHPMailer(true);
 
    $mail->SMTPDebug = 0; // 🔥 TEMP DEBUG
    //$mail->Debugoutput = 'html';
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailConfig['port'];

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'ParkMate OTP Verification';
    $mail->Body    = "
        <h2>ParkMate OTP</h2>
        <p>Your OTP is:</p>
        <h1 style='letter-spacing:3px;'>$otp</h1>
        <p>This OTP is valid for <b>5 minutes</b>.</p>
    ";
	$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ],
];

    $mail->send();

} catch (Exception $e) {
    die("OTP email failed: " . $mail->ErrorInfo);
}
// mail($email, "ParkMate OTP", "Your OTP is $otp (valid for 5 minutes)");

//Redirect to verify page
header("Location: verify_otp_html.php?type=$type");
exit;
?>