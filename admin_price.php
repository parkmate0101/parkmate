<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

/* ADD / UPDATE PRICE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $v_type = $_POST['v_type'];

    $updates = [];
    $params  = [];
    $types   = "";

    if ($_POST['price_per_hour'] !== '') {
        $updates[] = "price_per_hour = ?";
        $params[]  = (int)$_POST['price_per_hour'];
        $types    .= "i";
    }

    if ($_POST['price_per_day'] !== '') {
        $updates[] = "price_per_day = ?";
        $params[]  = (int)$_POST['price_per_day'];
        $types    .= "i";
    }
     /*// 🚫 nothing entered
    if (empty($updates)) {
        header("Location: admin_price.php");
        exit;
    }*/

    // Ensure row exists
    $conn->query("
        INSERT IGNORE INTO pricing (v_type) VALUES ('$v_type')
    ");

    if (!empty($updates)) {
        $sql = "UPDATE pricing SET " . implode(", ", $updates) . " WHERE v_type = ?";
        $stmt = $conn->prepare($sql);

        $params[] = $v_type;
        $types   .= "s";

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
}

/* FETCH PRICES */
$prices = [];
$res = $conn->query("SELECT * FROM pricing");
while ($row = $res->fetch_assoc()) {
    $prices[$row['v_type']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Pricing</title>
<link rel="stylesheet" href="css/admin_man_slot2.css">
</head>

<body class="admin-page">
<div class="admin-layout">

<aside class="admin-sidebar">
  <h2 class="admin-logo">ParkMate</h2>
  <h2 class="admin-logo">Admin</h2>
  <nav class="admin-nav">
    <a href="admin_dashh.php">Dashboard</a>
    <a href="admin_booking.php">Bookings</a>
    <a href="admin_man_slot.php">Slots</a>
    <a class="active">Pricing</a>
    <a href="admin_feedback.php">Feedback</a>
    <a href="admin_payment.php">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
    <a href="logout.php">Logout</a>
  </nav>
</aside>

<main class="admin-content">

<div class="page-header">
  <h1>Manage Pricing</h1><br>
</div>

<form method="POST">
<div class="add-slot">
  <select name="v_type" id="v_type" required>
  <option value="">Vehicle Type</option>
  <option value="2_wheeler">2 Wheeler</option>
  <option value="4_wheeler">4 Wheeler</option>
</select>

<input type="number" id="price_per_hour" name="price_per_hour" placeholder="Price / Hour">
<input type="number" id="price_per_day" name="price_per_day" placeholder="Price / Day">
  <button class="btn">Save</button>
</div>
</form>

<div class="table-container">
<table class="admin-table">
<tr>
  <th>Vehicle</th>
  <th>Per Hour</th>
  <th>Per Day</th>
  <th>Action</th>
</tr>
<?php foreach ($prices as $type => $p): ?>
<tr>
  <td><?= htmlspecialchars($type) ?></td>
  <td>₹<?= $p['price_per_hour'] ?></td>
  <td>₹<?= $p['price_per_day'] ?></td>
  <td>
    <button 
      type="button"
      class="btn btn-small"
      onclick="editPrice(
        '<?= $type ?>',
        '<?= $p['price_per_hour'] ?>',
        '<?= $p['price_per_day'] ?>'
      )"
    >
      Edit
    </button>
  </td>
</tr>
<?php endforeach; ?>
</table>
</div>

</main>
</div>
<script>
function editPrice(type, hour, day) {
  document.getElementById("v_type").value = type;
  document.getElementById("price_per_hour").value = hour;
  document.getElementById("price_per_day").value = day;

  window.scrollTo({ top: 0, behavior: "smooth" });
}
</script>
</body>
</html>