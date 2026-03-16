<?php
session_start();
include "db_connect.php";
if (!isset($_SESSION['u_id'])) {
    header("Location: login1.php");
    exit;
}
$uid = $_SESSION['u_id'];
/* 🔐 DB-level enforcement */
$stmt = $conn->prepare("SELECT password FROM users WHERE u_id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* ❌ Password already set → block page */
if (!empty($user['password'])) {
    $redirect = $_SESSION['post_login_redirect'] ?? 'index.php';
    unset($_SESSION['post_login_redirect']);
    header("Location: $redirect");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // 🔐 Password validation (clear & readable)

    if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long";
    header("Location: set_password.php");
    exit;
    }

    if (!preg_match('/[A-Z]/', $password)) {
    $_SESSION['error'] = "Password must contain at least one uppercase letter";
    header("Location: set_password.php");
    exit;
}

    if (!preg_match('/[a-z]/', $password)) {
    $_SESSION['error'] = "Password must contain at least one lowercase letter";
    header("Location: set_password.php");
    exit;
}

    if (!preg_match('/[0-9]/', $password)) {
    $_SESSION['error'] = "Password must contain at least one number";
    header("Location: set_password.php");
    exit;
}
    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: set_password.php");
        exit;
    }
        
    /*if (!preg_match('/[@$!%*#?&]/', $password)) {
    $_SESSION['error'] = "Password must contain at least one special character (@ $ ! % * # ? &)";
    header("Location: set_password.php");
    exit;
    }*/
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE users SET password=? WHERE u_id=? AND password IS NULL
    ");
    $stmt->bind_param("si", $hash, $uid);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        die("Password already set or invalid request");
    }

    $_SESSION['success'] = "Password set successfully";
   // header("Location: confirmation.php");
   // exit;
   $redirect = $_SESSION['post_login_redirect'] ?? 'index.php';
   unset($_SESSION['post_login_redirect']);
   header("Location: $redirect");
   exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Set Password | ParkMate</title>
  <link rel="stylesheet" href="css/set_password.css">
</head>
<body>

<div class="container">
    <div class="left">
        <img src="images/loginimage.png" alt="Parking Image">
    </div>

    <div class="right">
        <h2 class="logo">Park<span>Mate</span></h2>
        <h1 class="signup-title">SET PASSWORD</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color:red; text-align:center;">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </p>
        <?php endif; ?>

        <form method="POST" onsubmit="return validatePassword();">
            <input type="password" name="password" placeholder="NEW PASSWORD" class="input-box" required>
            <input type="password" name="confirm" placeholder="CONFIRM PASSWORD" class="input-box" required>
            <button type="submit" class="btn login-btn"> SAVE PASSWORD </button>
        </form>
    </div>
</div>
<script src="js/set_password.js"></script>

</body>
</html>