<?php
require 'db.php';

echo "<h3>DB: {$database}</h3>";

$tableCheck = $conn->query("SHOW TABLES LIKE 'orders'");
if(!$tableCheck || $tableCheck->num_rows === 0){
    echo "Orders table not found in this database.";
    exit();
}

echo "<h4>Orders columns</h4>";
$colResult = $conn->query("SHOW COLUMNS FROM orders");
while($row = $colResult->fetch_assoc()){
    echo $row['Field'] . " (" . $row['Type'] . ")<br>";
}
