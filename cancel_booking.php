<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['u_id'])) {
    header("Location: login1.php");
    exit;
}

$conn->begin_transaction();

try {
    $u_id = $_SESSION['u_id'];
    $b_id = intval($_GET['b_id']);

    $stmt = $conn->prepare("
        SELECT b.*, s.slot_id
        FROM booking b
        JOIN slot s ON b.slot_id = s.slot_id
        WHERE b.b_id = ? AND b.u_id = ? AND b.b_status = 'Active'
    ");
    $stmt->bind_param("ii", $b_id, $u_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();

    if (!$booking) {
        throw new Exception("Invalid booking");
    }

    $current_time = time();
    $start_time   = strtotime($booking['start_time']);

    if ($current_time >= $start_time) {
        throw new Exception("Booking already started");
    }

    $hours_left = ($start_time - $current_time) / 3600;
    $total  = $booking['total_amount'];
    $refund = 0;

    if ($hours_left > 24) {
        $refund = $total;
    } elseif ($hours_left > 12) {
        $refund = $total * 0.5;
    }

    $reason = "User cancelled";

    $upd = $conn->prepare("
    UPDATE booking
    SET b_status = 'Cancelled',
        cancelled_at = NOW(),
        refund_amount = ?,
        cancel_reason = ?
    WHERE b_id = ?");
    $upd->bind_param("dsi", $refund, $reason, $b_id);
    $upd->execute();

    $slotUpd = $conn->prepare("
    UPDATE slot 
    SET slot_status = 'Available' 
    WHERE slot_id = ?
");
$slotUpd->bind_param("i", $booking['slot_id']);
$slotUpd->execute();

    if ($refund > 0) {
        $ref = $conn->prepare("
            INSERT INTO refunds (b_id, u_id, amount, refund_status)
            VALUES (?, ?, ?, 'pending')
        ");
        $ref->bind_param("iid", $b_id, $u_id, $refund);
        $ref->execute();
    }

    $conn->commit();
    header("Location: my_bookings.php?cancel=success");
    exit;

} catch (Exception $e) {
    $conn->rollback();
    die("❌ Cancellation failed");
}
?>