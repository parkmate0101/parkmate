<?php
session_start();
include "db_connect.php";

$booking_id = $_SESSION['booking_id'] ?? null;

if (!$booking_id) {
    header("Location: slot.php");
    exit;
}

/* ===== FETCH BOOKING DETAILS ===== */
$stmt = $conn->prepare("
    SELECT b.*, s.slot_num, v.v_num, v.v_type, u.email, u.contact AS mobile
    FROM booking b
    JOIN slot s ON b.slot_id = s.slot_id
    JOIN vehicle v ON b.v_id = v.v_id
    JOIN users u ON b.u_id = u.u_id
    WHERE b.b_id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$data = $result->fetch_assoc();

/* Store session values locally */
$slot         = $data['slot_num'];
$start_time   = $data['start_time'];
$end_time     = $data['end_time'];
$email        = $data['email'];
$mobile       = $data['mobile'];
$vehicle      = $data['v_num'];
$v_type       = $data['v_type'];
$total_amount = $data['total_amount'];

/* Clear session values for next booking */
unset(
    $_SESSION['slot'],
    $_SESSION['start_time'],
    $_SESSION['end_time'],
    $_SESSION['email'],
    $_SESSION['mobile'],
    $_SESSION['vehicle'],
    $_SESSION['v_type'],
    $_SESSION['total_amount'],
    $_SESSION['booking_id']
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success</title>
    <link rel="stylesheet" href="css/success.css">
    <style>
body{
background-color:#0f172a;
background-image:
linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
background-size:40px 40px;
min-height:100vh;
}
</style>
</head>
<body>

<div class="success-container">
    <div class="success-header">
        <h2>Booking Confirmed!</h2>
        <p>Your parking slot has been successfully booked.</p>
    </div>

    <div class="ticket-details">
        <p><strong>Booking ID:</strong> <?= $booking_id ?></p>
        <p><strong>Parking Slot:</strong> <?= $slot ?></p>
        <p><strong>Date:</strong> <?= date("d M Y", strtotime($start_time)) ?></p>
        <p><strong>Time:</strong><?= date("h:i A", strtotime($start_time)) ?>
        -<?= date("h:i A", strtotime($end_time)) ?></p>
        <p><strong>Email:</strong> <?= $email ?></p>
        <p><strong>Mobile:</strong> <?= $mobile ?></p>
        <p><strong>Vehicle Number:</strong> <?= $vehicle ?></p>
        <p><strong>Vehicle Type:</strong> <?= $v_type ?></p>
        <p><strong>Total Amount:</strong> ₹<?= $total_amount ?></p>
    </div>

    <!--<div class="qr-code">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $booking_id ?>" alt="QR Code">
        <p>Scan this QR at the parking entrance</p>
    </div>-->
    <a href="index.php">Go to Home</a>
</div>
</body>
</html>