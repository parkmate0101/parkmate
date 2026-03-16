<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    header("Location: admin_man_slot.php");
    exit;
}
/* ===== HANDLE SLOT ACTIONS (ADD / DELETE / BLOCK / ENABLE) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ADD SLOT */
    if ($_POST['action'] === 'add') {
        $slot_num  = strtoupper(trim($_POST['slot_num']));
        $slot_type = $_POST['slot_type'];

        if ($slot_num && in_array($slot_type, ['2_wheeler','4_wheeler'])) {
            $stmt = $conn->prepare("
                INSERT IGNORE INTO slot (slot_num, slot_type)
                VALUES (?,?)
            ");
            $stmt->bind_param("ss", $slot_num, $slot_type);
            $stmt->execute();
        }
    }

    /* DELETE SLOT */
    if ($_POST['action'] === 'delete') {
        $slot_id = (int)$_POST['slot_id'];

        // check bookings
        $stmt = $conn->prepare("
            SELECT 1 FROM booking WHERE slot_id=? LIMIT 1
        ");
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $conn->query("DELETE FROM slot WHERE slot_id=$slot_id");
        }
    }

    /* BLOCK / ENABLE SLOT */
    if ($_POST['action'] === 'block' || $_POST['action'] === 'enable') {
        $slot_id = (int)$_POST['slot_id'];
        $status  = $_POST['action'] === 'block' ? 'Blocked' : 'Available';

        $conn->query("
            UPDATE slot SET slot_status='$status'
            WHERE slot_id=$slot_id
        ");
    }

    header("Location: admin_man_slot.php");
    exit;
}

$where = [];

if (!empty($_GET['slot_num'])) {
    $slotNum = $conn->real_escape_string($_GET['slot_num']);
    $where[] = "s.slot_num LIKE '%$slotNum%'";
}

if (!empty($_GET['slot_type'])) {
    $type = $conn->real_escape_string($_GET['slot_type']);
    $where[] = "s.slot_type = '$type'";
}

if (!empty($_GET['slot_status'])) {
    $status = $_GET['slot_status'] === 'Booked'
        ? "EXISTS (SELECT 1 FROM booking b WHERE b.slot_id=s.slot_id AND b.b_status='Active')"
        : "NOT EXISTS (SELECT 1 FROM booking b WHERE b.slot_id=s.slot_id AND b.b_status='Active')";
    $where[] = $status;
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

$sql = "
SELECT s.slot_id, s.slot_num, s.slot_type,
CASE 
    WHEN s.slot_status = 'Blocked' THEN 'Blocked'
    WHEN EXISTS (
        SELECT 1 FROM booking b 
        WHERE b.slot_id = s.slot_id 
        AND b.b_status = 'Active'
    ) THEN 'Booked'
    ELSE 'Available'
END AS real_status
FROM slot s
$whereSQL
ORDER BY LEFT(s.slot_num,1),
CAST(SUBSTRING(s.slot_num,2) AS UNSIGNED)
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Slots | ParkMate</title>
  <link rel="stylesheet" href="css/admin_man_slot2.css">
</head>

<body class="admin-page">

  <div class="admin-layout">

    <!-- ===== SIDEBAR ===== -->
  <aside class="admin-sidebar">
  <h2 class="admin-logo">ParkMate</h2>
    <h2 class="admin-logo">Admin</h2>

    <nav class="admin-nav">
      <a href="admin_dashh.php" >Dashboard</a>
      <a href="admin_booking.php">Bookings</a>
      <a href="admin_man_slot.php" class="active">Slots</a>
      <a href="admin_price.php">Pricing</a>
      <a href="admin_feedback.php">Feedback</a>
      <a href="admin_payment.php">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
      <a href="logout.php">Logout</a>
    </nav>
  </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="admin-content">

      <!-- Page Header -->
      <div class="page-header">
        <h1>Manage Slots</h1>
        <p>Add, enable or disable parking slots</p>
      </div>

      <!-- ===== ADD SLOT SECTION ===== -->
       <form class="add-slot" method="POST" action="admin_man_slot.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="slot_num" placeholder="Slot ID (A13)" required>

  <select name="slot_type" required>
    <option value="">Select Type</option>
    <option value="2_wheeler">2 Wheeler</option>
    <option value="4_wheeler">4 Wheeler</option>
  </select>

  <button class="btn" type="submit">Add Slot</button>
</form>

      <!-- ===== FILTER BAR ===== -->
<form class="filter-bar" method="GET">
  <input type="text" name="slot_num" placeholder="Slot ID (A1)"
         value="<?= htmlspecialchars($_GET['slot_num'] ?? '') ?>">

  <select name="slot_type">
    <option value="">All Types</option>
    <option value="2_wheeler"   <?= (@$_GET['slot_type']=='2_wheeler')?'selected':'' ?>>2_wheeler</option>
    <option value="4_wheeler"  <?= (@$_GET['slot_type']=='4_wheeler')?'selected':'' ?>>4_wheeler</option>
  </select>

  <select name="slot_status">
    <option value="">All Status</option>
    <option value="Available" <?= (@$_GET['slot_status']=='Available')?'selected':'' ?>>Available</option>
    <option value="Booked"    <?= (@$_GET['slot_status']=='Booked')?'selected':'' ?>>Booked</option>
  </select>

  <button class="btn" type="submit">Filter</button>
  <a href="admin_man_slot.php" class="btn">Reset</a>
</form>

      <!-- ===== SLOTS TABLE ===== -->
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Slot ID</th>
              <th>Type</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['slot_num']) ?></td>
            <td><?= htmlspecialchars($row['slot_type']) ?></td>

            <!-- STATUS(FIXED)-->
            <td>
            <?php if ($row['real_status'] === 'Booked'): ?>
              <span class="badge cancelled">Booked</span>
            <?php elseif ($row['real_status'] === 'Blocked'): ?>
              <span class="badge completed">Blocked</span>
            <?php else: ?>
              <span class="badge active">Available</span>
            <?php endif; ?>
            </td>

            <!-- ACTION-->
             <td>
<?php if ($row['real_status'] === 'Booked'): ?>

    <button class="btn btn-small" disabled>In Use</button>

<?php elseif ($row['real_status'] === 'Blocked'): ?>

    <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="enable">
        <input type="hidden" name="slot_id" value="<?= $row['slot_id'] ?>">
        <button class="btn btn-small">Enable</button>
    </form>

<?php else: ?>

    <form method="POST" style="display:inline">
        <input type="hidden" name="action" value="block">
        <input type="hidden" name="slot_id" value="<?= $row['slot_id'] ?>">
        <button class="btn btn-small">Block</button>
    </form>

    <form method="POST" style="display:inline"
          onsubmit="return confirm('Delete this slot permanently?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="slot_id" value="<?= $row['slot_id'] ?>">
        <button class="btn btn-small" style="background:#dc2626">Delete</button>
    </form>

<?php endif; ?>
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