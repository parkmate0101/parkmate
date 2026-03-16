<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if(!isset($_GET['uid'])){
    header("Location: login1.php");
    exit();
}

$uid = $_GET['uid'];
?>

<!DOCTYPE html>
<html>
<head>

<style>

body{
font-family:Arial;
background:#0f172a;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.card{
background:white;
padding:30px;
border-radius:10px;
width:350px;
text-align:center;
}

input{
width:100%;
padding:10px;
margin:10px 0;
border:1px solid #ccc;
border-radius:5px;
}

button{
width:100%;
padding:10px;
background:#2563eb;
color:white;
border:none;
border-radius:5px;
cursor:pointer;
}

</style>

</head>

<body>

<div class="card">

<h2>Enter OTP</h2>

<?php
if(isset($_GET['error'])){
    if($_GET['error'] == "invalid_otp"){
        echo "<p style='color:red;'>Invalid or Expired OTP</p>";
    }

    if($_GET['error'] == "blocked"){
        echo "<p style='color:red;'>Too many wrong attempts. Try again after 10 minutes.</p>";
    }
}
?>

<form action="check_reset_otp.php" method="POST">

<input type="hidden" name="uid" value="<?php echo $uid; ?>">

<input type="text" name="otp" placeholder="Enter OTP" required>

<button type="submit">Verify OTP</button>

</form>

</div>

</body>
</html>