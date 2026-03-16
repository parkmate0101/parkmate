<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST['fname']);
    $phone = trim($_POST['phone']);
    $email = strtolower(trim($_POST['email']));

    // ❌ Empty check
    if ($name === "" || $phone === "" || $email === "") {
        $_SESSION['error'] = "All fields are required";
        $_SESSION['old']   = $_POST;   // ⭐ SAVE OLD DATA
        header("Location: signup.php");
        exit;
    }

    // ❌ Name validation
    if (!preg_match('/^[A-Za-z ]{3,50}$/', $name)) {
        $_SESSION['error'] = "Name must contain only letters and spaces (min 3 chars)";
        $_SESSION['old']   = $_POST;
        header("Location: signup.php");
        exit;
    }

    // ❌ Phone validation
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['error'] = "Phone number must be exactly 10 digits";
        $_SESSION['old']   = $_POST;
        header("Location: signup.php");
        exit;
    }

    // ❌ Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address";
        $_SESSION['old']   = $_POST;
        header("Location: signup.php");
        exit;
    }
    
    // ❌ phone number exists
    $stmt = $conn->prepare("SELECT u_id FROM users WHERE contact=?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();
     
    if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Phone number already registered";
    header("Location: signup.php");
    exit;
    } 

    // ❌ Email exists
    $stmt = $conn->prepare("SELECT u_id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already registered";
        $_SESSION['old']   = $_POST;
        header("Location: signup.php");
        exit;
    }

    // ✅ Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (name, contact, email)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sss", $name, $phone, $email);
    $stmt->execute();
   /* $_SESSION['u_id']=$stmt->insert_id;
    
    if (isset($_SESSION['post_login_redirect'])) {
    header("Location: " . $_SESSION['post_login_redirect']);
    unset($_SESSION['post_login_redirect']);
    exit;
}*/
    // ✅ Clear old values after success
    unset($_SESSION['old']);

    $_SESSION['signup_uid']   = $stmt->insert_id;
    $_SESSION['signup_email'] = $email;

    header("Location: send_otp.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ParkMate</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>

<div class="container">
    <div class="left">
        <img src="images/loginimage.png">
    </div>

    <div class="right">
        <h2 class="logo">Park<span>Mate</span></h2>
        <h1 class="signup-title">SIGN UP</h1>

        <!-- 🔴 SERVER ERROR -->
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color:red; text-align:center;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateSignup();">

            <input type="text" name="fname" placeholder="FULL NAME" class="input-box" 
            value="<?= $_SESSION['old']['fname'] ?? '' ?>" required>
            <small class="error"></small>

            <input type="text" name="phone" placeholder="PHONE" class="input-box" 
            value="<?= $_SESSION['old']['phone'] ?? '' ?>" required>
            <small class="error"></small>

            <input type="email" name="email" placeholder="EMAIL" class="input-box" 
            value="<?= $_SESSION['old']['email'] ?? '' ?>" required>
            <small class="error"></small>

            <button type="submit" class="btn signup-btn" >Register</button>

            <p class="already">Already have an account?</p>
            <a href="login1.php" class="btn login-btn">LOG-IN</a>
        </form>
    </div>
</div>

<script src="js/signup.js"></script>
</body>
</html>