<?php
session_start();
include "db_connect.php";

/* session check – same logic as dashboard */
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | ParkMate</title>
<link rel="stylesheet" href="css/user_dashboard.css">
<link rel="stylesheet" href="css/my_bookings.css">
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
        <a class="active">My Bookings</a>
        <a href="user_profile.php">Profile</a>
		<!--<a href="cancel_booking.php">Cancel Booking</a>-->
		<a href="user_feedback.php">Feedback</a>
        <a href="user_payments.php">Payments</a>
    </aside>

    <div class="main-content">
        <h1>My Bookings</h1>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Slot</th>
                        <th>Vehicle Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $stmt = $conn->prepare("
                SELECT b.b_id, s.slot_num, b.v_type, b.start_time, b.end_time, b.b_status
                FROM booking b JOIN slot s ON b.slot_id = s.slot_id WHERE b.u_id = ?
                ORDER BY b.b_id DESC ");
                $stmt->bind_param("i", $u_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                    <tr>
                        <td><?php echo $row['b_id']; ?></td>
                        <td><?php echo $row['slot_num']; ?></td>
                        <td><?php echo strtoupper($row['v_type']); ?></td>
                        <td><?php echo date("d-m-Y H:i", strtotime($row['start_time'])); ?></td>
                        <td><?php echo date("d-m-Y H:i", strtotime($row['end_time'])); ?></td>
                        <td class="status <?php echo strtolower($row['b_status']); ?>">
                            <?php echo $row['b_status']; ?>
                        </td>
                        <td>
                            <?php if ($row['b_status'] == 'Cancelled'): ?> Cancelled
                            <?php elseif ($row['b_status'] == 'Active' && strtotime($row['start_time']) > time()): ?>
                            <a href="cancel_booking.php?b_id=<?= $row['b_id'] ?>" 
                            onclick="return confirm('Cancel booking?')">Cancel</a>
                            <?php else: ?> — <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="6" class="no-data">No bookings found</td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>