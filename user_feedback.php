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
/* Fetch completed bookings without feedback */
$bookings = $conn->query("
    SELECT b.b_id 
    FROM booking b
    LEFT JOIN feedback f ON b.b_id = f.b_id
    WHERE b.u_id = '$u_id'
      AND b.b_status = 'Completed'
      AND f.f_id IS NULL
");

/* Insert feedback */
if (isset($_POST['submit_feedback'])) {
    $b_id = $_POST['b_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO feedback (b_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $b_id, $rating, $comment);
    $stmt->execute();

    header("Location: user_feedback.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard | ParkMate</title>
<link rel="stylesheet" href="css/user_dashboard.css">
<link rel="stylesheet" href="css/user_feedback.css">
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
		<a class="active">Feedback</a>
        <a href="user_payments.php">Payments</a>
    </aside>
<!-- MAIN CONTENT -->
    <div class="main-content">
        <h1>Give Feedback</h1>

        <?php if (isset($_GET['success'])) { ?>
            <p class="success-msg">Thank you for your feedback 😊</p>
        <?php } ?>

        <?php if ($bookings->num_rows > 0) { ?>
            <form method="post" class="feedback-form">

                <label>Select Booking</label>
                <select name="b_id" required>
                    <option value="">-- Select Booking ID --</option>
                    <?php while ($row = $bookings->fetch_assoc()) { ?>
                        <option value="<?= $row['b_id'] ?>">
                            Booking #<?= $row['b_id'] ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Rating</label>
                <select name="rating" required>
                    <option value="">-- Rating --</option>
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                    <option value="3">⭐⭐⭐ Good</option>
                    <option value="2">⭐⭐ Fair</option>
                    <option value="1">⭐ Poor</option>
                </select>

                <label>Comment</label>
                <textarea name="comment" placeholder="Write your experience..." required></textarea>

                <button type="submit" name="submit_feedback">Submit Feedback</button>
            </form>
        <?php } else { ?>
            <p class="no-data">No completed bookings available for feedback.</p>
        <?php } ?>
    </div>
    
</div>

</body>
</html>