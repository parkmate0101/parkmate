<?php
ob_start();
session_start();
include "db_connect.php";

/* Prevent direct access if slot not selected */
if (
    empty($_SESSION['slot']) ||
    empty($_SESSION['start_time']) ||
    empty($_SESSION['end_time'])
) {
    header("Location: slot.php");
    exit;
}

/* If user already logged in → skip 
if (!empty($_SESSION['u_id'])) {
    header("Location: confirmation.php");
    exit;
}*/

$error = "";

/* Handle form submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email  = strtolower(trim($_POST['email']));
    $email  = filter_var($email, FILTER_SANITIZE_EMAIL);
    $mobile = preg_replace("/[^0-9]/", "", trim($_POST['mobile']));

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = "Mobile number must be 10 digits";
    } else {

        $_SESSION['email']  = $email;
        $_SESSION['mobile'] = $mobile;

        // STRICT email check
        $stmt = $conn->prepare("
            SELECT u_id 
            FROM users 
            WHERE LOWER(TRIM(email)) = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // ✅ Existing user
            header("Location: confirmation.php");
            exit;
        } else {
            // ❌ New user
            $_SESSION['post_login_redirect'] = 'confirmation.php';
            header("Location: signup.php");
            exit;
        }
    }
}

/* Prefill values */
$email  = $_SESSION['email'] ?? '';
$mobile = $_SESSION['mobile'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Details</title>
<link rel="stylesheet" href="css/contact.css">
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
<div class="form-container">
    <h2>Enter Contact Details</h2>

    <?php if ($error): ?>
        <p style="color:red; text-align:center"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" novalidate>
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label>Mobile:</label>
        <input type="text" name="mobile" value="<?= htmlspecialchars($mobile) ?>" required>

        <button type="submit">Continue Booking</button>
    </form>
</div>

<script>
document.querySelector("form").addEventListener("submit", function(e) {
    const email = document.querySelector("[name='email']").value.trim();
    const mobile = document.querySelector("[name='mobile']").value.trim();

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Invalid email");
        e.preventDefault();
    } else if (!/^[0-9]{10}$/.test(mobile)) {
        alert("Mobile must be 10 digits");
        e.preventDefault();
    }
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>