<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $_SESSION['old'] = $_POST; // ⭐ Save input

    $email    = strtolower(trim($_POST['email']));
    $password = $_POST['password'] ?? '';

    /* ===== BASIC VALIDATION ===== */
    if (empty($email)) {
        $_SESSION['error'] = "Email is required";
        header("Location: login1.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: login1.php");
        exit;
    }

    /* ===== FETCH USER ===== */
    $stmt = $conn->prepare("
        SELECT u_id, role_id, password
        FROM users
        WHERE email=? AND is_verified=1
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['error'] = "Email not registered or not verified";
        header("Location: login1.php");
        exit;
    }

    $row = $result->fetch_assoc();

    /* ===== SECURITY HARDENING ===== */
    // ❌ Admin cannot login without password
    if ($row['role_id'] == 1 && empty($password)) {
        $_SESSION['error'] = "Admin must login using password";
        header("Location: login1.php");
        exit;
    }

    /* ================= PASSWORD LOGIN ================= */
    if (!empty($password)) {

        if (empty($row['password']) || !password_verify($password, $row['password'])) {

            $stmt = $conn->prepare("
                INSERT INTO login (u_id, login_type, login_status)
                VALUES (?, 'password', 'failed')
            ");
            $stmt->bind_param("i", $row['u_id']);
            $stmt->execute();

            $_SESSION['error'] = "Invalid credentials";
            header("Location: login1.php");
            exit;
        }

        // ✅ PASSWORD LOGIN SUCCESS
        session_regenerate_id(true);

        $_SESSION['u_id']    = $row['u_id'];
        $_SESSION['role_id'] = $row['role_id'];

        unset($_SESSION['old']); // ✅ CLEAR OLD FORM DATA

        $stmt = $conn->prepare("
            INSERT INTO login (u_id, login_type, login_status)
            VALUES (?, 'password', 'success')
        ");
        $stmt->bind_param("i", $row['u_id']);
        $stmt->execute();

        if ($row['role_id'] == 1) {
        header("Location: admin_dashh.php");
        } else {
        header("Location: user_dashboard.php");
        }
        exit;
    }

    /* ================= OTP LOGIN (NO PASSWORD) ================= */
    else {

        $_SESSION['login_uid']   = $row['u_id'];
        $_SESSION['login_email'] = $email;

        unset($_SESSION['old']); // ✅ CLEAR OLD FORM DATA

        header("Location: send_otp.php?type=login");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkMate Login</title>
    <link rel="stylesheet" href="css/login1.css">
</head>
<body>

<div class="container">
    <div class="left">
        <img src="images/loginimage.png" alt="Parking Image">
    </div>

    <div class="right">
        <h2 class="logo">Park<span>Mate</span></h2>
        <h1 class="login-title">LOGIN</h1>

        <!-- 🔴 SERVER ERROR -->
        <?php if(isset($_SESSION['error'])): ?>
            <p style="color:red; text-align:center;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login1.php" onsubmit="return validateLogin();">

            <input type="email" name="email" placeholder="EMAIL" class="input-box"
            value="<?= $_SESSION['old']['email'] ?? '' ?>"  required>
            <small class="error"></small>

            <input type="password" name="password" placeholder="PASSWORD (Admin only or OTP login if empty)" 
            class="input-box">
            <small class="error"></small>
            <a href="forgot_password.php"><p class="forgot_password">Forgot Password?</a></p> <br>
            <button type="submit" class="btn login-btn">LOGIN</button>

            <p class="signup-text">Not registered yet?</p>
            <a href="signup.php" class="btn signup-btn">Sign-UP</a>
        </form>
    </div>
</div>
<script src="js/login.js"></script>
</body>
</html>