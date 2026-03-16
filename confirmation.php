<?php
session_start();
include "db_connect.php";

/* ===== BLOCK DIRECT ACCESS ===== */
if (
    !isset(
        $_SESSION['slot'],
        $_SESSION['start_time'],
        $_SESSION['end_time'],
        $_SESSION['email'],
        $_SESSION['mobile'],
        $_SESSION['vehicle'],
        $_SESSION['v_type']
    )
) {
    header("Location: contact.php");
    exit;
}

/* ===== FETCH USER ===== */
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT u_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "<script>alert('Please register first'); window.location='register.php';</script>";
    exit;
}

$row = $res->fetch_assoc();
$_SESSION['u_id'] = $row['u_id'];

/* ===== CALCULATE TOTAL AMOUNT ===== */
$start_time = $_SESSION['start_time'];
$end_time   = $_SESSION['end_time'];
$booking_type = $_SESSION['booking_type'];

$start = new DateTime($start_time);
$end   = new DateTime($end_time);

$stmt = $conn->prepare("SELECT price_per_hour, price_per_day FROM pricing WHERE v_type = ?");
$stmt->bind_param("s", $_SESSION['v_type']);
$stmt->execute();
$priceRow = $stmt->get_result()->fetch_assoc();

if ($booking_type === 'full-day') {
    $days = $start->diff($end)->days + 1;
    $total_amount = $days * $priceRow['price_per_day'];
} else {
    $hours = ceil(($end->getTimestamp() - $start->getTimestamp()) / 3600);
    $total_amount = $hours * $priceRow['price_per_hour'];
}

$_SESSION['total_amount'] = $total_amount;

/* ===== CONFIRM BOOKING BUTTON ===== */
if (isset($_POST['confirm_booking'])) {
    header("Location: payment.php"); // Booking will be inserted after payment
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="css/confirmation.css">	
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
<div class="confirmation-container">
    <h1>Booking Summary</h1>

    <div class="booking-details">
        <p><strong>Slot:</strong> <?= htmlspecialchars($_SESSION['slot']) ?></p>
        <p><strong>Date:</strong> 
        <?= date("d M Y", strtotime($_SESSION['start_time'])) ?>
        <?php if ($_SESSION['booking_type'] === 'full-day'): ?>- 
        <?= date("d M Y", strtotime($_SESSION['end_time'])) ?>
        <?php endif; ?>
        </p>
        <p><strong>Time:</strong> <?= date("h:i A", strtotime($_SESSION['start_time'])) ?> - <?= date("h:i A", strtotime($_SESSION['end_time'])) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></p>
        <p><strong>Mobile:</strong> <?= htmlspecialchars($_SESSION['mobile']) ?></p>
        <p><strong>Vehicle:</strong> <?= htmlspecialchars($_SESSION['vehicle']) ?></p>
        <p><strong>Vehicle Type:</strong> <?= htmlspecialchars($_SESSION['v_type']) ?></p>
        <p><strong>Total Amount:</strong> ₹<?= $total_amount ?></p>
    </div>

    <form method="POST">
        <button type="submit" name="confirm_booking">Proceed to Payment</button>
    </form>
</div>
</body>
</html>