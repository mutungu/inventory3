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

<div style="display:flex; flex-wrap:wrap;">

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px; width:200px;'>";
        echo "<h3>" . $row['name'] . "</h3>";
        echo "<img src='images/" . $row['image'] . "' width='150'><br>";
        echo "<p>Price: KES " . $row['price'] . "</p>";
        echo "</div>";
    }
} else {
    echo "No products found";
}
?>

</div>

</body>
</html>