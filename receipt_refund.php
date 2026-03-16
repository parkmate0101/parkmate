<?php
require_once __DIR__ . '/fpdf/fpdf.php';
include "db_connect.php";
session_start();

/* ================= AUTH ================= */
if (!isset($_SESSION['role_id']) && !isset($_SESSION['u_id'])) {
    exit("Unauthorized");
}

$refund_id = intval($_GET['refund_id']);

/* ================= FETCH DATA ================= */
$q = $conn->prepare("
    SELECT 
        r.refund_id,
        r.amount,
        r.created_at,
        u.name,
        b.b_id
    FROM refunds r
    JOIN booking b ON r.b_id = b.b_id
    JOIN users u ON r.u_id = u.u_id
    WHERE r.refund_id = ? AND r.refund_status = 'processed'
");
$q->bind_param("i", $refund_id);
$q->execute();
$data = $q->get_result()->fetch_assoc();

if (!$data) {
    exit("Invalid refund");
}

/* ================= RECEIPT NUMBER ================= */
$rec_no = "RFND-" . $refund_id;

/* ================= SAVE RECEIPT (IDEMPOTENT) ================= */
$chk = $conn->prepare("
    SELECT refund_rec_id FROM refund_receipt WHERE refund_id = ?
");
$chk->bind_param("i", $refund_id);
$chk->execute();
$chk->store_result();

if ($chk->num_rows == 0) {
    $ins = $conn->prepare("
        INSERT INTO refund_receipt (refund_id, rec_no)
        VALUES (?, ?)
    ");
    $ins->bind_param("is", $refund_id, $rec_no);
    $ins->execute();
}

/* ================= PDF ================= */
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Refund Receipt',0,1,'C');

$pdf->Ln(10);
$pdf->SetFont('Arial','',12);

$pdf->Cell(0,8,'Refund Receipt No: '.$rec_no,0,1);
$pdf->Cell(0,8,'Customer Name: '.$data['name'],0,1);
$pdf->Cell(0,8,'Booking ID: '.$data['b_id'],0,1);
$pdf->Cell(0,8,'Refund Amount: Rs. '.number_format($data['amount'],2),0,1);
$pdf->Cell(0,8,'Refund Date: '.date("d-m-Y H:i", strtotime($data['created_at'])),0,1);

$pdf->Ln(15);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,8,'This is a system-generated refund receipt.',0,1,'C');

$pdf->Output('D', $rec_no.'.pdf');
?>