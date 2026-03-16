<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include "db_connect.php";

/* ------- 1. Identify OTP type & user---- */
$type = $_GET['type'] ?? 'register';

if ($type === 'register') {
    $uid = $_SESSION['signup_uid'] ?? null;
} elseif ($type === 'login') {
    $uid = $_SESSION['login_uid'] ?? null;
} else {
    die("Invalid OTP type");
}

if (!$uid) {
    die("Session expired. Please retry.");
}

/* --------- 2. Validate OTP input ----- */
if (!isset($_POST['otp']) ||!preg_match('/^[0-9]{6}$/', $_POST['otp'])) {
    $_SESSION['otp_error'] = "Invalid OTP format";
    header("Location: verify_otp_html.php?type=$type");
    exit;
}

$otp = trim($_POST['otp']);

/* ------ 3. Fetch latest valid OTP -------- */
$stmt = $conn->prepare("
    SELECT otp_id, otp_code, attempt_count, blocked_until
    FROM otp_verification
    WHERE u_id = ?
      AND purpose = ?
      AND is_used = 0
      AND expires_at > NOW()
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param("is", $uid, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['otp_error'] = "OTP expired or invalid";
    header("Location: verify_otp_html.php?type=$type");
    exit;
}

$row = $result->fetch_assoc();

/* ----- 4. Block check ---- */
if (!empty($row['blocked_until']) && strtotime($row['blocked_until']) > time()) {
    die("Too many attempts. Try again after 15 minutes.");
}

/* ----- 5. OTP MATCH ----------- */
if ($otp === $row['otp_code']) {

    // ✅ Mark OTP as used
    $stmt = $conn->prepare("
        UPDATE otp_verification
        SET is_used = 1
        WHERE otp_id = ?
    ");
    $stmt->bind_param("i", $row['otp_id']);
    $stmt->execute();

    // ✅ Mark user verified (register flow)
    if ($type === 'register') {
        $stmt = $conn->prepare("
            UPDATE users SET is_verified = 1 WHERE u_id = ?
        ");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
    }

    //✅ Fetch role
    $stmt = $conn->prepare("
        SELECT role_id FROM users WHERE u_id = ?
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // ✅ Create session
    session_regenerate_id(true);
    $_SESSION['u_id']    = $uid;
    $_SESSION['role_id'] = $user['role_id'];

    // ✅ Login audit
    $stmt = $conn->prepare("
        INSERT INTO login (u_id, login_type, login_status)
        VALUES (?, 'otp', 'success')
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();

    // ✅ Cleanup temp session data
    unset(
        $_SESSION['signup_uid'],
        $_SESSION['signup_email'],
        $_SESSION['login_uid'],
        $_SESSION['login_email'],
        $_SESSION['debug_otp']
    );

// 🔐 Check password status
$stmt = $conn->prepare("SELECT password, role_id FROM users WHERE u_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* 🔥 ENFORCED FLOW */
if (empty($user['password'])) {
    // Preserve booking redirect if already set
    if (!isset($_SESSION['post_login_redirect'])) {
        $_SESSION['post_login_redirect'] = 'confirmation.php';
    }
    header("Location: set_password.php");
    exit;
}

/* ✅ Normal login redirect */
if ($user['role_id'] == 1) {
    header("Location: admin_dashh.php");
} else {
    header("Location: user_dashboard.php");
}
exit;
}

/* --- 6. WRONG OTP ---- */
$attempts = $row['attempt_count'] + 1;

if ($attempts >= 5) {

    $blockedUntil = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $stmt = $conn->prepare("
        UPDATE otp_verification
        SET attempt_count = ?, blocked_until = ?
        WHERE otp_id = ?
    ");
    $stmt->bind_param("isi", $attempts, $blockedUntil, $row['otp_id']);
    $stmt->execute();

    $_SESSION['otp_error'] = "Too many wrong attempts. Blocked for 15 minutes.";
    header("Location: verify_otp_html.php?type=$type");
    exit;

} else {

    $stmt = $conn->prepare("
        UPDATE otp_verification
        SET attempt_count = ?
        WHERE otp_id = ?
    ");
    $stmt->bind_param("ii", $attempts, $row['otp_id']);
    $stmt->execute();

    $_SESSION['otp_error'] = "Wrong OTP. Attempts left: " . (5 - $attempts);
    header("Location: verify_otp_html.php?type=$type");
    exit;
}
?>