<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

/* ================= FILTER LOGIC ================= */
$where = [];

if (!empty($_GET['from_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from_date'])) {
    $where[] = "DATE(b.start_time) >= '{$_GET['from_date']}'";
}

if (!empty($_GET['to_date'])) {
    $where[] = "DATE(b.end_time) <= '{$_GET['to_date']}'";
}

if (!empty($_GET['slot_id']) && ctype_digit($_GET['slot_id'])) {
    $where[] = "b.slot_id = '{$_GET['slot_id']}'";
}

$allowedStatus = ['Active','Completed','Cancelled'];
if (!empty($_GET['status']) && in_array($_GET['status'],$allowedStatus)) {
    $where[] = "b.b_status = '{$_GET['status']}'";
}

$whereSQL = '';
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Bookings | ParkMate</title>
  <link rel="stylesheet" href="css/admin_booking.css">
</head>

<body class="admin-page">

<div class="admin-layout">

  <!-- ===== SIDEBAR ===== -->
  <aside class="admin-sidebar">
    <h2 class="admin-logo">ParkMate</h2>
    <h2 class="admin-logo">Admin</h2>

    <nav class="admin-nav">
      <a href="admin_dashh.php">Dashboard</a>
      <a href="admin_booking.php" class="active">Bookings</a>
      <a href="admin_man_slot.php">Slots</a>
      <a href="admin_price.php">Pricing</a>
      <a href="admin_feedback.php">Feedback</a>
      <a href="admin_payment.php">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

  <!-- ===== MAIN CONTENT ===== -->
  <main class="admin-content">

    <div class="page-header">
      <h1>Bookings</h1>
      <p>View and manage all parking bookings</p>
    </div>

    <!-- ===== FILTER BAR ===== -->
    <form method="GET">
      <div class="filter-bar">

        <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? '' ?>">
        <input type="date" name="to_date" value="<?= $_GET['to_date'] ?? '' ?>">

        <select name="slot_id">
          <option value="">All Slots</option>
          <?php
          $slotRes = $conn->query("SELECT slot_id, slot_num FROM slot");
          while ($s = $slotRes->fetch_assoc()) {
              $sel = (($_GET['slot_id'] ?? '') == $s['slot_id']) ? 'selected' : '';
              echo "<option value='{$s['slot_id']}' $sel>{$s['slot_num']}</option>";
          }
          ?>
        </select>

        <select name="status">
          <option value="">All Status</option>
          <option value="Active" <?= (($_GET['status'] ?? '')=='Active')?'selected':'' ?>>Active</option>
          <option value="Completed" <?= (($_GET['status'] ?? '')=='Completed')?'selected':'' ?>>Completed</option>
          <option value="Cancelled" <?= (($_GET['status'] ?? '')=='Cancelled')?'selected':'' ?>>Cancelled</option>
        </select>

        <button class="btn" type="submit">Filter</button>
        <a href="admin_booking.php" class="btn">Reset</a>
      </div>
    </form>

    <!-- ===== BOOKINGS TABLE ===== -->
    <div class="table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>User</th>
            <th>Slot ID</th>
            <th>Date & Time</th>
            <th>Duration</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
<?php
$result = $conn->query("SELECT b.b_id, b.u_id, b.slot_id, b.start_time, b.end_time, b.b_status 
    FROM booking b $whereSQL ORDER BY b.b_id DESC ");

while ($row = $result->fetch_assoc()):
    $duration = max(1, round((strtotime($row['end_time']) - strtotime($row['start_time'])) / 3600));
?>
          <tr>
            <td>#B<?= $row['b_id'] ?></td>
            <td>User <?= $row['u_id'] ?></td>
            <td><?= $row['slot_id'] ?></td>
            <td><?= date("d M Y, h:i A", strtotime($row['start_time'])) ?></td>
            <td><?= $duration ?> hrs</td>
            <td>
              <span class="badge <?= strtolower($row['b_status']) ?>">
                <?= $row['b_status'] ?>
              </span>
            </td>
          </tr>
<?php endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

</body>
</html>