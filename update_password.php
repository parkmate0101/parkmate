<?php
session_start();

$conn = new mysqli("localhost","root","","parking_system");

if(!isset($_SESSION['reset_uid'])){
header("Location: login1.php");
exit();
}

$uid = $_SESSION['reset_uid'];

$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

/* PASSWORD MATCH CHECK */

if($password !== $confirm){

$_SESSION['error'] = "Passwords do not match";
header("Location: reset_password.php");
exit();

}

/* STRONG PASSWORD VALIDATION */

if(strlen($password) < 6){

$_SESSION['error'] = "Password must be at least 6 characters";
header("Location: reset_password.php");
exit();

}

if(!preg_match('/[A-Z]/',$password)){

$_SESSION['error'] = "Password must contain uppercase letter";
header("Location: reset_password.php");
exit();

}

if(!preg_match('/[a-z]/',$password)){

$_SESSION['error'] = "Password must contain lowercase letter";
header("Location: reset_password.php");
exit();

}

if(!preg_match('/[0-9]/',$password)){

$_SESSION['error'] = "Password must contain number";
header("Location: reset_password.php");
exit();

}

/* UPDATE PASSWORD */

$hash = password_hash($password, PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password='$hash' WHERE u_id='$uid'");

/* DESTROY RESET SESSION */

unset($_SESSION['reset_uid']);

?>
<!DOCTYPE html>
<html>
<head>
<title>Password Updated</title>

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

h2{
color:#16a34a;
}

button{
width:100%;
padding:10px;
background:#2563eb;
color:white;
border:none;
border-radius:5px;
cursor:pointer;
margin-top:15px;
}

button:hover{
background:#1e40af;
}

</style>

</head>

<body>

<div class="card">

<h2>Password Updated Successfully</h2>

<p>You can now login with your new password.</p>

<a href="login1.php">
<button>Go to Login</button>
</a>

</div>

</body>
</html>