<?php
session_start();
include "db_connect.php";

if (
    !isset(
        $_SESSION['slot'],
        $_SESSION['start_time'],
        $_SESSION['end_time'],
        $_SESSION['vehicle'],
        $_SESSION['v_type'],
        $_SESSION['u_id'],
        $_SESSION['total_amount']
    )
) {
    header("Location: slot.php");
    exit;
}

/* ===== FETCH SLOT ID ===== */
$slotNum = $_SESSION['slot'];
$stmt = $conn->prepare("SELECT slot_id FROM slot WHERE slot_num = ? LIMIT 1");
$stmt->bind_param("s", $slotNum);
$stmt->execute();
$slot_id = $stmt->get_result()->fetch_assoc()['slot_id'];

/* ===== FETCH OR INSERT VEHICLE ===== */
$vNum = $_SESSION['vehicle'];
$u_id = $_SESSION['u_id'];

$stmt = $conn->prepare("SELECT v_id FROM vehicle WHERE v_num=? AND u_id=?");
$stmt->bind_param("si", $vNum, $u_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $v_id = $res->fetch_assoc()['v_id'];
} else {
    $stmt = $conn->prepare("INSERT INTO vehicle (u_id,v_num,v_type) VALUES (?,?,?)");
    $stmt->bind_param("iss", $u_id, $vNum, $_SESSION['v_type']);
    $stmt->execute();
    $v_id = $conn->insert_id;
}

/* ===== SLOT AVAILABILITY CHECK ===== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS cnt 
    FROM booking 
    WHERE slot_id = ?
    AND b_status = 'Active'
    AND start_time < ?
    AND end_time > ?
");
$stmt->bind_param("iss", $slot_id, $_SESSION['end_time'], $_SESSION['start_time']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row['cnt'] > 0) {
    die("Slot already booked. Please try again.");
}

/* ===== INSERT BOOKING ===== */
$booking_date = date('Y-m-d');

$stmt = $conn->prepare("
    INSERT INTO booking
    (u_id, v_id, v_type, slot_id, start_time, end_time, total_amount, payment_status, b_status, booking_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', 'Active', ?)
");

/* ✅ FIXED TYPE STRING */
$stmt->bind_param(
    "iissssds",
    $u_id,
    $v_id,
    $_SESSION['v_type'],
    $slot_id,
    $_SESSION['start_time'],
    $_SESSION['end_time'],
    $_SESSION['total_amount'],
    $booking_date
);
$stmt->execute();

$booking_id = $stmt->insert_id;

/* ===== PAYMENT ===== */
$stmt = $conn->prepare("
    INSERT INTO payment (b_id, paid_amount, payment_type, mode, pay_status)
    VALUES (?, ?, 'advance', 'upi', 'paid')
");
$stmt->bind_param("id", $booking_id, $_SESSION['total_amount']);
$stmt->execute();

$payment_id = $conn->insert_id;

/* ===== RECEIPT ===== */
$rec_no = 'REC' . time() . rand(100,999);
$stmt = $conn->prepare("INSERT INTO receipt (p_id, rec_no) VALUES (?, ?)");
$stmt->bind_param("is", $payment_id, $rec_no);
$stmt->execute();

$_SESSION['booking_id'] = $booking_id;
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful</title>
<link rel="stylesheet" href="css/payment_success.css">
</head>
<body>

<div class="success-card">
  <div class="checkmark">✓</div>
  <h2>Payment Successful</h2>
  <p>₹<?= $_SESSION['total_amount'] ?> received</p>
</div>

<script>
setTimeout(() => {
    window.location.href = "success.php";
}, 2500);
</script>
</body>
</html>