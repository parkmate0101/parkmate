<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['u_id'])) {
    header("Location: login1.php");
    exit;
}

$u_id = $_SESSION['u_id'];
/* Fetch user name */
$stmt = $conn->prepare("SELECT name FROM users WHERE u_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

$user_name = $user['name'];   // ✅ NOW DEFINED
$stmt = $conn->prepare("
    SELECT p.p_id, p.paid_amount, p.mode, p.pay_status, p.pay_date, b.b_id, r.rec_no, rf.refund_id,
        rf.amount AS refund_amount, rf.refund_status
    FROM payment p
    JOIN booking b ON p.b_id = b.b_id
    LEFT JOIN receipt r ON p.p_id = r.p_id
    LEFT JOIN refunds rf ON rf.b_id = b.b_id
    WHERE b.u_id = ?
    ORDER BY p.pay_date DESC
");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Payments</title>
<link rel="stylesheet" href="css/user_dashboard.css">
<link rel="stylesheet" href="css/user_payments.css">
</head>
<body>
<!-- NAVBAR -->
<nav class="navbar">

    <div class="brand">🚗 ParkMate</div>
    <div class="nav-user">
	<div class="user-header">
    <span class="welcome-text">
        Welcome, <?= htmlspecialchars($user_name) ?>
    </span>
	</div>
        <span class="user-icon">👤</span>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</nav>

<!-- DASHBOARD -->
<div class="dashboard">

    <!-- LEFT MENU -->
    <aside class="menu">
        <a href="user_dashboard.php">Dashboard</a>
        <a href="timeslot.php">Book Slot</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="user_profile.php">Profile</a>
		<a href="user_feedback.php">Feedback</a>
		<a class="active">Payments</a>
    </aside>
<!-- MAIN CONTENT -->
    <div class="main-content">
        <h1>My Payments</h1>

<table border="1" width="95%" align="center" cellpadding="10">
<tr>
    <th>Booking ID</th>
    <th>Amount</th>
    <th>Mode</th>
    <th>Status</th>
    <th>Payment Receipt</th>
    <th>Date</th>
    <th>Refund</th>
    <th>Refund Status</th>
    <th>Refund Receipt</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td>#B<?= $row['b_id'] ?></td>
    <td>₹<?= number_format($row['paid_amount'],2) ?></td>
    <td><?= strtoupper($row['mode']) ?></td>
    <td class="status <?= strtolower($row['pay_status']) ?>"><?= ucfirst($row['pay_status']) ?></td>
    <td><?php if ($row['rec_no']) { ?>
    <a href="receipt_pdf.php?p_id=<?= $row['p_id'] ?>" class="btn-download">Download</a>
    <?php } else { echo '—'; } ?>
    </td>
    <td><?= date("d M Y, h:i A", strtotime($row['pay_date'])) ?></td>
    <td>
    <?php
    if ($row['refund_amount'] > 0) {
        echo "₹" . number_format($row['refund_amount'], 2);
    } else {
        echo "—";
    }
    ?>
</td>

<td>
    <?php
    if ($row['refund_status']) {
        echo ucfirst($row['refund_status']);
    } else {
        echo "—";
    }
    ?>
</td>
<td>
<?php
if ($row['refund_status'] == 'processed') {
    echo '<a href="receipt_refund.php?refund_id='.$row['refund_id'].'" class="btn-download">Download</a>';
} elseif ($row['refund_status'] == 'pending') {
    echo '<span style="color:#f59e0b;">Processing</span>';
} else {
    echo '—';
}
?>
</td>

</tr>
<?php endwhile; ?>
<?php if ($result->num_rows == 0): ?>
<tr>
    <td colspan="6" style="text-align:center;color:#6b7280;">
        No payment records found
    </td>
</tr>
<?php endif; ?>
</table>
<br>
</div>
</body>
</html>