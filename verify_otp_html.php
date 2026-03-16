<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - ParkMate</title>
    <link rel="stylesheet" href="css/otp.css">
</head>
<body>

<div class="otp-container">
    <h2>Verify OTP</h2>
    <p>Enter the 6-digit OTP sent to your email</p>
        <?php if (isset($_SESSION['otp_error'])): ?>
            <p style="color:red; font-size:14px; margin-top:10px;">
            <?= $_SESSION['otp_error']; unset($_SESSION['otp_error']); ?>
            </p>
        <?php endif; ?>

    <!-- 🔐 LOCALHOST OTP DISPLAY (DEBUG ONLY) -->
    <?php if (isset($_SESSION['debug_otp'])): ?>
        <p style="color: green; font-weight: bold;">
            OTP (Localhost only): <?= $_SESSION['debug_otp'] ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="verify_otp.php?type=<?php echo $_GET['type']; ?>">
        <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" placeholder="Enter OTP" required>
        <button type="submit">Verify</button>
    </form>

    <p class="resend">Did not receive OTP?
        <a id="resendLink" href="send_otp.php?type=<?php echo $_GET['type']; ?>">Resend</a>
    </p>
</div>
<script>
const resend = document.getElementById("resendLink");
let time = 30;

resend.style.pointerEvents = "none";
resend.style.opacity = "0.5";

const timer = setInterval(() => {
  time--;
  resend.textContent = "Resend (" + time + "s)";
  if (time <= 0) {
    clearInterval(timer);
    resend.textContent = "Resend";
    resend.style.pointerEvents = "auto";
    resend.style.opacity = "1";
  }
}, 1000);
</script>
</body>
</html>