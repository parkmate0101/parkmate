<?php
$host = "localhost";
$user = "root"; 
$pass = "";
$dbname = "parking_system";
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$conn->query("
    UPDATE booking
    SET b_status = 'Completed'
    WHERE b_status = 'Active'
    AND end_time < NOW()
");
//echo "Expired bookings completed";
?>