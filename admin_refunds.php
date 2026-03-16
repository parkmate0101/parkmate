<?php
session_start();
include "db_connect.php";

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

/* ================= FETCH REFUNDS ================= */
$query = "
    SELECT 
        r.refund_id,
        r.amount,
        r.created_at AS refund_date,
        r.refund_status,
        b.b_id,
        b.v_type,
        b.start_time,
        b.end_time,
        u.name AS user_name,
        p.paid_amount
    FROM refunds r
    JOIN booking b ON r.b_id = b.b_id
    JOIN users u ON r.u_id = u.u_id
    JOIN payment p ON p.b_id = b.b_id
    ORDER BY r.created_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Refunds | ParkMate</title>
<link rel="stylesheet" href="css/admin_dashh.css">
</head>

<body class="admin-page">

<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
    <h2 class="admin-logo">🚗 ParkMate</h2>
    <h2 class="admin-logo">Admin</h2>

    <nav class="admin-nav">
        <a href="admin_dashh.php">Dashboard</a>
        <a href="admin_booking.php">Bookings</a>
        <a href="admin_man_slot.php">Slots</a>
        <a href="admin_price.php">Pricing</a>
        <a href="admin_feedback.php">Feedback</a>
        <a href="admin_payment.php">Payments</a>
        <a class="active">Refunds</a>
        <a href="logout.php">Logout</a>
    </nav>
</aside>

<!-- CONTENT -->
<main class="admin-content">

<div class="page-header">
    <h1>Refund Transactions</h1>
    <p>All cancelled bookings with refund details</p>
</div>

<div class="table-container">
<table class="admin-table">
<thead>
<tr>
    <th>Refund ID</th>
    <th>Booking ID</th>
    <th>User</th>
    <th>Vehicle</th>
    <th>Booking Time</th>
    <th>Paid Amount</th>
    <th>Refunded</th>
    <th>Refund Date</th>
    <th>Status</th>
    <th>Action</th> 
    <th>Receipt</th>
</tr>
</thead>

<tbody>
<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['refund_id'] ?></td>
        <td><?= $row['b_id'] ?></td>
        <td><?= htmlspecialchars($row['user_name']) ?></td>
        <td><?= strtoupper($row['v_type']) ?></td>
        <td>
            <?= date("d-m-Y H:i", strtotime($row['start_time'])) ?> –
            <?= date("H:i", strtotime($row['end_time'])) ?>
        </td>
        <td>₹<?= number_format($row['paid_amount'], 2) ?></td>
        <td>₹<?= number_format($row['amount'], 2) ?></td>
        <td><?= date("d-m-Y H:i", strtotime($row['refund_date'])) ?></td>

        <!-- STATUS -->
        <td><?= ucfirst($row['refund_status']) ?></td>

        <!-- ACTION -->
        <td>
            <?php if ($row['refund_status'] == 'pending'): ?>
                <form method="POST" action="process_refund.php" style="margin:0;">
                    <input type="hidden" name="refund_id" value="<?= $row['refund_id'] ?>">
                    <button type="submit" class="btn-process">
                        Mark Processed
                    </button>
                </form>
            <?php else: ?>
                <span style="color:green;font-weight:bold;">Processed</span>
            <?php endif; ?>
        </td>
        <td>
<?php if ($row['refund_status'] == 'processed'): ?>
    <a 
      href="receipt_refund.php?refund_id=<?= $row['refund_id'] ?>" 
      class="btn-download"
    >
      Download
    </a>
<?php else: ?>
    —
<?php endif; ?>
</td>

    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr>
    <td colspan="10" style="text-align:center;">No refunds found</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

</main>
</div>

</body>
</html>