<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if(!isset($_SESSION['reset_uid'])){
header("Location: login1.php");
exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Reset Password</title>

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
background:#16a34a;
color:white;
border:none;
border-radius:5px;
cursor:pointer;
}

small{
color:red;
display:block;
text-align:left;
}

</style>

</head>

<body>

<div class="card">

<h2>Reset Password</h2>

<?php
if(isset($_SESSION['error'])){
echo "<p style='color:red'>".$_SESSION['error']."</p>";
unset($_SESSION['error']);
}
?>

<form action="update_password.php" method="POST" onsubmit="return validatePassword();">

<input type="password" name="password" placeholder="New Password" required>
<small></small>

<input type="password" name="confirm" placeholder="Confirm Password" required>
<small></small>

<button type="submit">Update Password</button>

</form>

</div>

<script src="js/set_password.js"></script>

</body>
</html>