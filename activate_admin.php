<?php
session_start();
require_once __DIR__ . "/models/connection.php";

if(!isset($_SESSION["id"])){
    echo "Please log in first. <a href='index.php'>Go to login</a>";
    exit();
}

$db = Connection::connect();
$stmt = $db->prepare("UPDATE users SET profile = 'Administrator', status = 1 WHERE id = :id");
$stmt->bindParam(":id", $_SESSION["id"], PDO::PARAM_INT);
$stmt->execute();

$_SESSION["profile"] = "Administrator";
echo "Admin access activated for your account. <a href='index.php'>Go back</a>";
