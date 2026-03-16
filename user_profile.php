<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['u_id'])) {
    header("Location: login1.php");
    exit;
}

$u_id = $_SESSION['u_id'];

/* Fetch user data */
$user = $conn->query("
    SELECT name, email, contact 
    FROM users 
    WHERE u_id = '$u_id'
")->fetch_assoc();


$user_name = $user['name'];   // ✅ NOW DEFINED
/* Update profile */
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];

    $conn->query("
        UPDATE users 
        SET name='$name', contact='$contact' 
        WHERE u_id='$u_id'
    ");

    header("Location: user_profile.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | ParkMate</title>
<link rel="stylesheet" href="css/user_dashboard.css">
<link rel="stylesheet" href="css/profile.css">
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
        <a class="active">Profile</a>
		<a href="user_feedback.php">Feedback</a>
        <a href="user_payments.php">Payments</a>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h1>My Profile</h1>

        <?php if (isset($_GET['updated'])) { ?>
            <p class="success-msg">Profile updated successfully ✔</p>
        <?php } ?>

        <form method="post" class="profile-form">
            <label>Name</label>
            <input type="text" name="name" value="<?= $user['name'] ?>" required>

            <label>Email</label>
            <input type="email" value="<?= $user['email'] ?>" readonly>

            <label>Contact</label>
            <input type="text" name="contact" value="<?= $user['contact'] ?>" required>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>

</div>

</body>
</html>