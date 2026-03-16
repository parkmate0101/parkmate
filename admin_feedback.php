<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header("Location: login1.php");
    exit;
}

/* ✅ DELETE FEEDBACK USING POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $id = intval($_POST['f_id']); // secure
    $stmt=$conn->prepare("DELETE FROM feedback WHERE f_id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    header("Location: admin_feedback.php");
    exit;
}

/* FETCH FEEDBACK */
$feedbacks = mysqli_query($conn, "
    SELECT f.*, b.b_id, u.name AS user_name FROM feedback f
    JOIN booking b ON f.b_id = b.b_id
    JOIN users u ON b.u_id = u.u_id
    ORDER BY f.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Feedback</title>
<link rel="stylesheet" href="css/admin_feedback.css">
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
    <a href="admin_price.php">Pricing</a>
    <a class="active">Feedback</a>
    <a href="admin_payment.php">Payments</a>
        <a href="admin_refunds.php">Refunds</a>
    <a href="logout.php">Logout</a>
  </nav>
</aside>

<main class="admin-content">

<div class="page-header">
  <h1>Manage Feedback</h1><br>

<div class="table-container">
<table class="admin-table">
<tr>
  <th>ID</th>
  <th>Booking ID</th>
  <th>User Name</th>
  <th>Rating</th>
  <th>Comment</th>
  <th>Date</th>
  <th>Action</th>
</tr>

<?php if (mysqli_num_rows($feedbacks) > 0) { ?>
    <?php while ($row = mysqli_fetch_assoc($feedbacks)) { ?>
    <tr>
    <td><?= $row['f_id'] ?></td>
    <td><?= $row['b_id'] ?></td>
    <td><?= htmlspecialchars($row['user_name']) ?></td>
    <td class="rating-stars">
        <?php
        for ($i = 1; $i <= 5; $i++) {
            echo ($i <= $row['rating']) ? "⭐" : "☆";
        }
        ?>
    </td>
    <td class="feedback-comment"><?= htmlspecialchars($row['comment']) ?></td>
    <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
    <td>
        <form method="POST" onsubmit="return confirm('Delete this feedback?');">
            <input type="hidden" name="f_id" value="<?= $row['f_id'] ?>">
            <button type="submit" name="delete_feedback" class="btn">Delete</button>
        </form>
    </td>
</tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="6" style="text-align:center;">No feedback found</td>
    </tr>
<?php } ?>

</table>
</div>

</main>
</div>
</body>
</html>