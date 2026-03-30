<?php
session_start();
require 'db.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){
            $_SESSION['user'] = $user;
            header("Location: index.php");
        } else {
            echo "Wrong password!";
        }
    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body {font-family:Arial; background:#222; color:white;}
.container {width:300px; margin:auto; margin-top:100px;}
input,button {width:100%; padding:10px; margin:10px 0;}
button {background:orange; border:none;}
</style>
</head>
<body>

<div class="container">
<h2>Login</h2>

<form method="POST">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
</form>

</div>

</body>
</html>