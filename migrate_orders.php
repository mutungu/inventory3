<?php
require 'db.php';

echo "<h3>Orders table migration</h3>";

// Ensure orders table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'orders'");
if(!$tableCheck || $tableCheck->num_rows === 0){
    echo "Orders table not found. Please create it first.<br>";
    exit();
}

// Get existing columns
$cols = [];
$colResult = $conn->query("SHOW COLUMNS FROM orders");
while($row = $colResult->fetch_assoc()){
    $cols[] = $row['Field'];
}

$alter = [];

if(!in_array('product_name', $cols)){
    $alter[] = "ADD COLUMN product_name VARCHAR(255) NULL";
}
if(!in_array('price', $cols)){
    $alter[] = "ADD COLUMN price DECIMAL(10,2) NULL";
}
if(!in_array('quantity', $cols)){
    $alter[] = "ADD COLUMN quantity INT NULL";
}
if(!in_array('user_id', $cols)){
    $alter[] = "ADD COLUMN user_id INT NULL";
}
if(!in_array('created_at', $cols)){
    $alter[] = "ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP";
}

if(empty($alter)){
    echo "No changes needed — required columns already exist.<br>";
} else {
    $sql = "ALTER TABLE orders " . implode(", ", $alter);
    if($conn->query($sql)){
        echo "Orders table updated successfully.<br>";
    } else {
        echo "Failed to update orders table: " . $conn->error . "<br>";
    }
}

echo "<br>Current columns:<br>";
$colResult = $conn->query("SHOW COLUMNS FROM orders");
while($row = $colResult->fetch_assoc()){
    echo $row['Field'] . "<br>";
}
