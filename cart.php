<?php
session_start();

// Handle remove BEFORE output (very important)
if(isset($_POST['remove'])) {
    $index = $_POST['index'];
    unset($_SESSION['cart'][$index]);

    // reindex array
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
</head>
<body>

<h2>Your Cart</h2>

<?php
$total = 0;

if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

    foreach($_SESSION['cart'] as $index => $item) {

        $total += $item['price'];

        echo "<p>
                " . $item['name'] . " - KES " . $item['price'] . "
                
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='index' value='$index'>
                    <button type='submit' name='remove'>Remove</button>
                </form>
              </p>";
    }

    echo "<h3>Total: KES " . $total . "</h3>";

} else {
    echo "Your cart is empty";
}
?>

<br>
<a href="index.php">← Continue Shopping</a>

</body>
</html>