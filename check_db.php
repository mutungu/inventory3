<?php
require 'db.php';

$result = $conn->query('SHOW TABLES');
while($row = $result->fetch_array()) {
    echo $row[0] . "\n";
}

echo "\nUsers table:\n";
$result2 = $conn->query('SELECT * FROM users');
if($result2) {
    while($row = $result2->fetch_assoc()) {
        echo $row['email'] . ' - ' . $row['password'] . ' - ' . $row['role'] . "\n";
    }
} else {
    echo "No users table or error\n";
}
?>