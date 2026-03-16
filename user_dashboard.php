<?php
session_start();

/* PREVENT BACK BUTTON ACCESS AFTER LOGOUT */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "db_connect.php";

if (!isset($_SESSION['u_id']) || $_SESSION['role_id'] != 2 || empty($_SESSION['u_id'])) {
    session_unset();
    session_destroy();
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
        <a class="active">Dashboard</a>
        <a href="timeslot.php">Book Slot</a>
        <a href="my_bookings.php">My Bookings</a>
        <a href="user_profile.php">Profile</a>
		<a href="user_feedback.php">Feedback</a>
        <a href="user_payments.php">Payments</a>
    </aside>

    <!-- CONTENT -->
    <main class="main">
        <h1>Welcome to Your Dashboard</h1>
        <p>Quick access to your parking activities.</p>

        <div class="box-container">
            <div class="box">
                <h3>Book Parking</h3>
                <p>Reserve parking slots instantly.</p>
                <a href="slot.php">Book Now</a>
            </div>

            <div class="box">
                <h3>My Bookings</h3>
                <p>Check your booking history.</p>
                <a href="my_bookings.php">View</a>
            </div>

            <div class="box">
                <h3>Profile</h3>
                <p>Update personal details.</p>
                <a href="profile.php">Edit</a>
            </div>
        </div>
    </main>

</div>
<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>