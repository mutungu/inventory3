<?php
require 'db.php';

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory 3</title>
</head>
<body>

<h2>Products</h2>

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div>";
        echo "<h3>" . $row['name'] . "</h3>";
        echo "<p>Price: KES " . $row['price'] . "</p>";
        echo "</div>";
    }
} else {
    echo "No products found";
}
?>

</body>
</html>