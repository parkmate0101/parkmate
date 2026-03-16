<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>

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
box-shadow:0 10px 25px rgba(0,0,0,0.3);
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

button:hover{
background:#1e40af;
}
</style>
</head>

<body>

<div class="card">

<h2>Forgot Password</h2>

<?php
if(isset($_SESSION['error'])){
echo "<p style='color:red;'>".$_SESSION['error']."</p>";
unset($_SESSION['error']);
}
?>

<form action="send_reset_otp.php" method="POST">

<input type="email" name="email" placeholder="Enter Email" required>

<button type="submit">Send OTP</button>

</form>

</div>

</body>
</html>