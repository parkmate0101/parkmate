<?php
session_start();
include "../db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login1.php");
    exit;
}

$id     = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

$allowed = ['Active','Completed','Canceled'];

if ($id && in_array($status, $allowed)) {
    $stmt = $conn->prepare("
        UPDATE booking SET b_status=? WHERE b_id=?
    ");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

header("Location: admin_booking.php");
exit;