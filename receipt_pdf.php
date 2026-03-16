<?php
require_once __DIR__ . '/fpdf/fpdf.php';
include "db_connect.php";
session_start();

if (!isset($_SESSION['u_id'])) exit;

$p_id = $_GET['p_id'];
$u_id = $_SESSION['u_id'];

$q = $conn->query("
SELECT p.paid_amount,p.pay_date,p.mode,r.rec_no,u.name,b.b_id
FROM payment p
JOIN booking b ON p.b_id=b.b_id
JOIN users u ON b.u_id=u.u_id
JOIN receipt r ON p.p_id=r.p_id
WHERE p.p_id='$p_id' AND b.u_id='$u_id'
");


$data = $q->fetch_assoc();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Parking Receipt',0,1,'C');

$pdf->Ln(10);
$pdf->SetFont('Arial','',12);

$pdf->Cell(0,8,'Receipt No: '.$data['rec_no'],0,1);
$pdf->Cell(0,8,'Customer: '.$data['name'],0,1);
$pdf->Cell(0,8,'Booking ID: '.$data['b_id'],0,1);
$pdf->Cell(0,8,'Amount Paid: Rs. '.$data['paid_amount'],0,1);
$pdf->Cell(0,8,'Payment Mode: '.$data['mode'],0,1);
$pdf->Cell(0,8,'Date: '.$data['pay_date'],0,1);

$pdf->Output('D','receipt_'.$data['rec_no'].'.pdf');
?>