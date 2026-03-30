<?php
require 'db.php';

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO users(name,email,password) VALUES('$name','$email','$password')");

    echo "Account created! <a href='login.php'>Login</a>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
body {font-family:Arial;}
.container {width:300px; margin:auto; margin-top:100px;}
input,button {width:100%; padding:10px; margin:10px 0;}
button {background:green; color:white;}
</style>
</head>

<body>

<div class="container">
<h2>Register</h2>

<form method="POST">
<input type="text" name="name" placeholder="Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="register">Register</button>
</form>

</div>

</body>
</html>