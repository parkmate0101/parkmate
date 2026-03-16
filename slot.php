<?php
session_start();
include "db_connect.php";
$_SESSION['vehicle'] = $_POST['vehicle'] ?? $_SESSION['vehicle'];
$_SESSION['v_type']  = $_POST['v_type'] ?? $_SESSION['v_type'];

/* ==== PROCESS TIME INPUT =============== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date'])) {
    $start_date = $_POST['start_date'] ?? '';

    if (!$start_date || strtotime($start_date) < strtotime(date('Y-m-d'))) {
    $_SESSION['time_error'] = "Past date not allowed";
    header("Location: timeslot.php");
    exit;
    }

    $_SESSION['booking_type'] = $_POST['booking_type'];

    if ($_POST['booking_type'] === 'full-day') {

        $_SESSION['start_time'] = $_POST['start_date'] . ' 00:00:00';
        $_SESSION['end_time']   = $_POST['end_date']   . ' 23:59:59';

    } else {

        $start = DateTime::createFromFormat(
            'Y-m-d h A',
            $_POST['start_date'].' '.$_POST['start_hour'].' '.$_POST['start_ampm']
        );

        $end = DateTime::createFromFormat(
            'Y-m-d h A',
            $_POST['start_date'].' '.$_POST['end_hour'].' '.$_POST['end_ampm']
        );

        if ($end <= $start) {
        $_SESSION['time_error'] = "Invalid time range selected";
        header("Location: timeslot.php");
        exit;
        }

        $_SESSION['start_time'] = $start->format('Y-m-d H:i:s');
        $_SESSION['end_time']   = $end->format('Y-m-d H:i:s');
    }
}

if (!isset($_SESSION['start_time'], $_SESSION['end_time'])) {
    header("Location: timeslot.php");
    exit();
}

$selectedStart = $_SESSION['start_time'];
$selectedEnd   = $_SESSION['end_time'];

/* === SAVE SLOT =============== */
if (isset($_POST['selectedSlot'])) {
    $_SESSION['slot'] = $_POST['selectedSlot'];
    header("Location: contact.php");
    exit();
}
    /*$conn->query("UPDATE booking SET b_status = 'Completed' WHERE end_time < NOW() AND b_status = 'Active'");*/
/* =====FETCH BOOKED SLOTS=============== */
$bookedSlots = [];

$stmt = $conn->prepare("
    SELECT s.slot_num 
    FROM slot s 
    JOIN booking b ON s.slot_id = b.slot_id 
    WHERE b.b_status='Active'
    AND b.start_time < ?
    AND b.end_time > ?
    AND s.slot_type = ?
");
$stmt->bind_param("sss", $selectedEnd, $selectedStart, $_SESSION['v_type']);
$stmt->execute();
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {
    $bookedSlots[] = $r['slot_num'];
}

/* FETCH ALL SLOTS */
$stmtAll = $conn->prepare("
    SELECT slot_num 
    FROM slot 
    WHERE slot_status != 'Blocked'
    AND slot_type = ?
    ORDER BY LEFT(slot_num,1),
    CAST(SUBSTRING(slot_num,2) AS UNSIGNED)
");
$stmtAll->bind_param("s", $_SESSION['v_type']);
$stmtAll->execute();
$result = $stmtAll->get_result();

$groups = [];
while ($r = $result->fetch_assoc()) {
    $groups[substr($r['slot_num'],0,1)][] = $r['slot_num'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Parking Slot | ParkMate</title>
<link rel="stylesheet" href="css/slot.css">

<style>
.slot.selected { background:#ff9800; color:#fff; }
.slot.booked { background:#ccc; cursor:not-allowed; }
body{
background-color:#0f172a;
background-image:
linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
background-size:40px 40px;
min-height:100vh;
}
.title { color:#ffffff; }
</style>
</head>

<body>
<?php if (isset($_SESSION['slot_error'])): ?>
<p style="color:red; text-align:center; margin-bottom:10px;">
    <?= $_SESSION['slot_error']; ?>
</p>
<?php unset($_SESSION['slot_error']); endif; ?>

<div class="parking-page">

<header class="page-header">
    <h1 class="title">Select Parking Slot</h1>
    <p><?= htmlspecialchars($selectedStart) ?> → <?= htmlspecialchars($selectedEnd) ?></p>
</header>

<form method="POST">
<input type="hidden" name="selectedSlot" id="selectedSlot">

<?php foreach ($groups as $group => $slots): ?>
<section class="slot-group">
    <h2>Slot <?= $group ?></h2>
    <div class="slots-grid">

    <?php foreach ($slots as $slotNum): 
        $isBooked = in_array($slotNum, $bookedSlots);
        $nextFree = null;

        if ($isBooked) {
            $stmt2 = $conn->prepare("
                SELECT MIN(end_time) AS next_free
                FROM booking
                WHERE slot_id = (
                    SELECT slot_id FROM slot WHERE slot_num = ?
                )
                AND end_time > ?
                AND b_status = 'Active'
            ");
            $stmt2->bind_param("ss", $slotNum, $selectedStart);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $row2 = $res2->fetch_assoc();

            if ($row2 && $row2['next_free']) {
                $nextFree = date("d M, h:i A", strtotime($row2['next_free']));
            }
        }
    ?>
        <button
            type="button"
            class="slot <?= $isBooked ? 'booked' : 'available' ?>"
            <?= $isBooked ? 'disabled' : '' ?>
            title="<?= $isBooked && $nextFree ? 'Available after: '.$nextFree : '' ?>"
            onclick="selectSlot(event,'<?= $slotNum ?>')">
            <?= $slotNum ?>
        </button>
    <?php endforeach; ?>

    </div>
</section>
<?php endforeach; ?>

<button type="submit" class="confirm-btn" disabled >Confirm Slot</button>
</form>

</div>
<script src="js/slot.js"></script>
</body>
</html>