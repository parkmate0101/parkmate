<?php
session_start();
include "db_connect.php";
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    die("Unauthorized access");
}
if (!isset($_POST['refund_id'])) {
    die("Invalid request");
}
$refund_id = intval($_POST['refund_id']);
$stmt = $conn->prepare("
    UPDATE refunds 
    SET refund_status = 'processed'
    WHERE refund_id = ?
");
$stmt->bind_param("i", $refund_id);
$stmt->execute();
header("Location: admin_refunds.php");
exit;
?>