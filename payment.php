<?php
session_start();
if (!isset($_SESSION['total_amount'])) {
    header("Location: slot.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="css/payment.css">
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
<div class="pay-box">
    <h2>Payment Page</h2>
    <p><strong>Amount:</strong> ₹<?php echo $_SESSION['total_amount']; ?></p>
    <form action="payment_success.php" method="POST">
        <button type="submit">Pay Now</button>
    </form>
</div>
</body>
</html>