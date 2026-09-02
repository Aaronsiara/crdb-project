<?php
session_start();

$username = "admin";
$password = "admin123";

if(isset($_POST['login']))
{
    if(
        $_POST['username'] === $username &&
        $_POST['password'] === $password
    )
    {
        $_SESSION['logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    }

    $error = "Invalid credentials";
}

if(!isset($_SESSION['logged_in']))
{
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#0f172a;
    font-family:Arial;
}
.login-box{
    width:350px;
    background:#fff;
    padding:30px;
    border-radius:15px;
}
input{
    width:100%;
    padding:12px;
    margin:10px 0;
}
button{
    width:100%;
    padding:12px;
    background:#007608;
    color:white;
    border:none;
}
</style>
</head>
<body>

<div class="login-box">
<h2>Segmentation Dashboard</h2>

<?php if(isset($error)) echo $error; ?>

<form method="POST">
<input type="text" name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button name="login">Login</button>
</form>
</div>

</body>
</html>

<?php
exit;
}
?>