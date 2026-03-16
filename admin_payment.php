<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$where = "";
$params = [];
$types  = "";

if ($from && $to) {
    $where = "WHERE DATE(p.pay_date) BETWEEN ? AND ?";
    $params = [$from, $to];
    $types = "ss";
}

$sql = "
SELECT 
    p.p_id,
    p.paid_amount,
    p.mode,
    p.pay_status,
    p.pay_date,
    b.b_id,
    u.email
FROM payment p
JOIN booking b ON p.b_id = b.b_id
JOIN users u ON b.u_id = u.u_id
$where
ORDER BY p.pay_date DESC
";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* Total Revenue */
$revenue = $conn->query("
    SELECT 
        IFNULL(SUM(p.paid_amount),0) 
        - IFNULL(SUM(
            CASE 
                WHEN r.refund_status = 'processed' THEN r.amount 
                ELSE 0 
            END
        ),0) AS revenue
    FROM payment p
    LEFT JOIN refunds r ON p.b_id = r.b_id
    WHERE p.pay_status='paid'
")->fetch_assoc()['revenue'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Payments | ParkMate</title>
<link rel="stylesheet" href="css/admin_payment.css">
</head>

<body class="admin-page">

<div class="admin-layout">

<!-- ===== SIDEBAR ===== -->
<aside class="admin-sidebar">
    <h2 class="admin-logo">🚗 ParkMate</h2>
    <h2 class="admin-logo">Admin</h2>

    <nav class="admin-nav">
        <a href="admin_dashh.php">Dashboard</a>
        <a href="admin_booking.php">Bookings</a>
        <a href="admin_man_slot.php">Slots</a>
        <a href="admin_price.php">Pricing</a>
        <a href="admin_feedback.php">Feedback</a>
        <a class="active">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
        <a href="logout.php">Logout</a>
    </nav>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<main class="admin-content">

<div class="page-header">
    <h1>Payments</h1>
    <p>Track all completed and pending payments</p>
</div>

<!-- ===== STATS CARD ===== -->
<div class="dashboard-cards">
    <div class="dashboard-card green">
        <p>Total Revenue</p>
        <h2>₹<?= number_format($revenue,2) ?></h2>
    </div>
</div>

<!-- ===== FILTER BAR ===== -->
<form method="GET">
<div class="filter-bar">
    <input type="date" name="from" value="<?= $from ?>">
    <input type="date" name="to" value="<?= $to ?>">
    <button class="btn">Filter</button>
    <a href="admin_payment.php" class="btn">Reset</a>
</div>
</form>

<!-- ===== PAYMENT TABLE ===== -->
<div class="table-container">
<table class="admin-table">
<thead>
<tr>
    <th>Payment ID</th>
    <th>Booking ID</th>
    <th>User</th>
    <th>Amount</th>
    <th>Mode</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>
<?php if ($result->num_rows > 0): ?>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td>#P<?= $row['p_id'] ?></td>
    <td>#B<?= $row['b_id'] ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td>₹<?= number_format($row['paid_amount'],2) ?></td>
    <td><?= strtoupper($row['mode']) ?></td>
    <td>
        <span class="badge <?= strtolower($row['pay_status']) ?>">
            <?= ucfirst($row['pay_status']) ?>
        </span>
    </td>
    <td><?= date("d M Y, h:i A", strtotime($row['pay_date'])) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="7" style="text-align:center;color:#6b7280;">
        No payment records found
    </td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

</main>
</div>

</body>
</html>