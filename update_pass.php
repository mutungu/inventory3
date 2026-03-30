<?php
require 'db.php';

$hashed = password_hash('1234', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hashed' WHERE email='admin@gmail.com'");
echo "Password updated using password_hash().";
?>
