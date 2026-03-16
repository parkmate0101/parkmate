<?php
session_start();
include "db_connect.php";

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

/* ================= FILTER INPUT ================= */
$filter     = $_GET['filter_option'] ?? 'select';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

/* ================= BASE CONDITIONS ================= */
$activeOnly = "b.b_status='Active'";

/* ================= DATE FILTERS ================= */
/* Separate filters for PAYMENT and BOOKING */
$paymentDateFilter = "";
$bookingDateFilter = "";

/* Custom date range */
if (!empty($start_date) && !empty($end_date)) {

    $paymentDateFilter = " AND DATE(p.pay_date) BETWEEN '$start_date' AND '$end_date'";
    $bookingDateFilter = " AND DATE(b.start_time) BETWEEN '$start_date' AND '$end_date'";

} else {

    switch ($filter) {

        case 'today':
            $paymentDateFilter = " AND DATE(p.pay_date) = CURDATE()";
            $bookingDateFilter = " AND DATE(b.start_time) = CURDATE()";
            break;

        case 'last5':
            $paymentDateFilter = " AND p.pay_date >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)";
            $bookingDateFilter = " AND b.start_time >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)";
            break;

        case 'month':
            $paymentDateFilter = "
                AND MONTH(p.pay_date)=MONTH(CURDATE())
                AND YEAR(p.pay_date)=YEAR(CURDATE())
            ";
            $bookingDateFilter = "
                AND MONTH(b.start_time)=MONTH(CURDATE())
                AND YEAR(b.start_time)=YEAR(CURDATE())
            ";
            break;

        case 'total':
        case 'select':
        default:
            // No filter
            break;
    }
}

/* ================= DASHBOARD DATA ================= */

/* Total Revenue (Completed Bookings) */
$qRevenue = $conn->query("
    SELECT 
        IFNULL(SUM(p.paid_amount),0) 
        - IFNULL(SUM(
            CASE 
                WHEN r.refund_status = 'processed' THEN r.amount 
                ELSE 0 
            END
        ),0) AS revenue
    FROM payment p
    JOIN booking b ON p.b_id = b.b_id
    LEFT JOIN refunds r ON b.b_id = r.b_id
    WHERE p.pay_status='paid'
    $paymentDateFilter
");
$revenue = $qRevenue->fetch_assoc()['revenue'];

/* Expected Revenue (Active Bookings) */
$qExpected = $conn->query("
    SELECT IFNULL(SUM(p.paid_amount),0) AS expected
    FROM payment p
    JOIN booking b ON p.b_id = b.b_id
    WHERE p.pay_status='paid'
    AND b.b_status='Active'
    $paymentDateFilter
");
$expected_revenue = $qExpected->fetch_assoc()['expected'];

/* Total Paid Bookings */
$qBookings = $conn->query("
    SELECT COUNT(*) AS total
    FROM payment p
    JOIN booking b ON p.b_id=b.b_id
    WHERE p.pay_status='paid'
    $paymentDateFilter
");
$total_bookings = $qBookings->fetch_assoc()['total'];

/* Active Bookings */
$qActive = $conn->query("
    SELECT COUNT(*) AS active
    FROM booking b
    WHERE $activeOnly
    $bookingDateFilter
");
$active_bookings = $qActive->fetch_assoc()['active'];

/* Total Slots */
$qSlots = $conn->query("SELECT COUNT(*) AS total FROM slot");
$total_slots = $qSlots->fetch_assoc()['total'];

/* Currently Booked Slots (Active + Time-based) */
$qBooked = $conn->query("
    SELECT COUNT(DISTINCT b.slot_id) AS booked
    FROM booking b
    WHERE b.b_status='Active'
    AND NOW() BETWEEN b.start_time AND b.end_time
    $bookingDateFilter
");
$booked_slots = $qBooked->fetch_assoc()['booked'];

/* Available Slots */
$available_slots = max(0, $total_slots - $booked_slots);

/* Registered Users */
$qUsers = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $qUsers->fetch_assoc()['total_users'];

/* Average Rating */
$qRating = $conn->query("
    SELECT ROUND(IFNULL(AVG(rating),0),1) AS avg_rating
    FROM feedback
");
$avg_rating = $qRating->fetch_assoc()['avg_rating'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | ParkMate</title>
<link rel="stylesheet" href="css/admin_dashh.css">
</head>

<body class="admin-page">

<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="admin-sidebar">
    <h2 class="admin-logo">🚗 ParkMate</h2>
    <h2 class="admin-logo">Admin</h2>

    <nav class="admin-nav">
        <a class="active">Dashboard</a>
        <a href="admin_booking.php">Bookings</a>
        <a href="admin_man_slot.php">Slots</a>
        <a href="admin_price.php">Pricing</a>
        <a href="admin_feedback.php">Feedback</a>
        <a href="admin_payment.php">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
        <a href="logout.php">Logout</a>
    </nav>
</aside>

<!-- CONTENT -->
<main class="admin-content">

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Overview of bookings, parking slots, and revenue</p>
</div>

<!-- FILTER -->
<div class="filter-section">
<form method="GET">
    <label>Filter:</label>
    <select name="filter_option">
        <option value="select">Select</option>
        <option value="today" <?= $filter=='today'?'selected':'' ?>>Today</option>
        <option value="last5" <?= $filter=='last5'?'selected':'' ?>>Last 5 Days</option>
        <option value="month" <?= $filter=='month'?'selected':'' ?>>This Month</option>
        <option value="total" <?= $filter=='total'?'selected':'' ?>>All Time</option>
    </select>

    <label>OR Custom Range:</label>
    <input type="date" name="start_date" value="<?= $start_date ?>">
    <input type="date" name="end_date" value="<?= $end_date ?>">

    <button type="submit" class="btn">Filter</button>
    <a href="admin_dashh.php" class="btn">Reset</a>
</form>
</div>
<br>
<!-- DASHBOARD CARDS -->
<div class="dashboard-cards">

<div class="dashboard-card red">
    <p>Total Revenue (Completed)</p>
    <h2>₹<?= number_format($revenue,2) ?></h2>
</div>

<div class="dashboard-card orange">
    <p>Expected Revenue (Active)</p>
    <h2>₹<?= number_format($expected_revenue,2) ?></h2>
</div>

<div class="dashboard-card red">
    <p>Registered Users</p>
    <h2><?= $total_users ?></h2>
</div>

<div class="dashboard-card orange">
    <p>Booked Slots</p>
    <h2><?= $booked_slots ?></h2>
</div>

<div class="dashboard-card red">
    <p>Available Slots</p>
    <h2><?= $available_slots ?></h2>
</div>

<div class="dashboard-card orange">
    <p>Total Slots</p>
    <h2><?= $total_slots ?></h2>
</div>

<div class="dashboard-card red">
    <p>Active Bookings</p>
    <h2><?= $active_bookings ?></h2>
</div>

<div class="dashboard-card orange">
    <p>Total Bookings</p>
    <h2><?= $total_bookings ?></h2>
</div>

<div class="dashboard-card red">
    <p>Average Rating</p>
    <h2>
        <?php
        for ($i = 1; $i <= 5; $i++) {
            echo ($i <= round($avg_rating)) ? "⭐" : "☆";
        }
        ?>
    </h2>
    <p><?= $avg_rating ?> out of 5</p>
</div>

</div>

</main>
</div>

</body>
</html>