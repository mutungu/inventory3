<?php
session_start();
require 'db.php';

// Handle Add to Cart FIRST (before any HTML)
if(isset($_POST['add_to_cart'])) {

    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];

    // Initialize cart if not set
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = [
        'id' => $product_id,
        'name' => $product_name
    ];

    // Redirect (VERY IMPORTANT)
    header("Location: index.php");
    exit();
}

// Fetch products
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
<a href="cart.php">View Cart</a>

<div style="display:flex; flex-wrap:wrap;">

<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px; width:200px;'>";
        echo "<h3>" . $row['name'] . "</h3>";
        echo "<img src='images/" . $row['image'] . "' width='150'><br>";
        echo "<p>Price: KES " . $row['price'] . "</p>";

        echo "<form method='POST'>
                <input type='hidden' name='product_id' value='" . $row['id'] . "'>
                <input type='hidden' name='product_name' value='" . $row['name'] . "'>
                <button type='submit' name='add_to_cart'>Add to Cart</button>
              </form>";

        echo "</div>";
    }
} else {
    echo "No products found";
}
?>

</div>

</body>
</html>