<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Time Slot | ParkMate</title>
    <link rel="stylesheet" href="css/timeslot.css">
    <style>
body{
background-color:#0f172a;
background-image:
linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
background-size:40px 40px;
min-height:100vh;
}

</style>
</head>
<body>

<div class="container">

    <h1>Select Parking Duration</h1>

    <form method="POST" action="slot.php" id="form">

        <!-- Booking Type -->
        <div class="section">
            <label class="label">Booking Type</label>
            <div class="radio-group">
                <label>
                    <input type="radio" name="booking_type" value="hourly" checked>Hourly Booking
                </label>
                <label>
                    <input type="radio" name="booking_type" value="full-day">Full-Day Booking
                </label>
            </div>
        </div>

        <!-- Date Selection -->
        <div class="section">
            <label class="label">Select Date</label>
            <div class="date-group">
                <input type="date" name="start_date" id="start_date" required>
                <span id="toText">to</span>
                <input type="date" name="end_date" id="end_date" required>
            </div>
        </div>

        <!-- Time Slot (12 Hour Format) -->
        <div class="section">
            <label class="label">Select Time Slot (Hourly)</label>

            <div class="time-group">
                <!-- From Time -->
                <select name="start_hour" required>
                    <?php for ($i=1;$i<=12;$i++) echo "<option>$i</option>"; ?>
                </select>

                <select name="start_ampm" required>
                    <option>AM</option>
                    <option>PM</option>
                </select>

                <span>to</span>

                <!-- To Time -->
                <select name="end_hour" required>
                   <?php for ($i=1;$i<=12;$i++) echo "<option>$i</option>"; ?>
                </select>

                <select name="end_ampm" required>
                    <option>AM</option>
                    <option>PM</option>
                </select>
            </div><br>

                <!-- VEHICLE TYPE -->
            <div class="section">
                <label class="label">Vehicle Type</label>
                <select name="v_type" required>
                    <option value="">Select</option>
                    <option value="2_wheeler">2 Wheeler</option>
                    <option value="4_wheeler">4 Wheeler</option>
                </select><br>
            </div>

                <!-- VEHICLE NUMBER -->
            <div class="section">
                <label class="label">Vehicle Number</label>
                <input type="text" name="vehicle" placeholder="GJ01AB1234" required>
            </div>

            <!-- JS error -->
            <p id="timeError" style="color:red; display:none; font-size:14px;"></p>

            <!-- PHP error -->
            <?php if (isset($_SESSION['time_error'])): ?>
            <p style="color:red; font-size:14px;"><?= $_SESSION['time_error']; ?></p>
            <?php unset($_SESSION['time_error']); endif; ?>

            <p class="note">
                Hourly parking allowed between 10:00 AM – 10:00 PM
            </p>
        </div>

        <!-- Next Button -->
        <button type="submit" class="btn">Proceed to Slot Selection</button>
    </form>

</div>

<script src="js/timeslot.js"></script>
</body>
</html>