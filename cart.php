<?php
session_start();

// Increase quantity
if(isset($_POST['increase'])) {
    $_SESSION['cart'][$_POST['index']]['quantity']++;
}

// Decrease quantity
if(isset($_POST['decrease'])) {
    $index = $_POST['index'];

    if($_SESSION['cart'][$index]['quantity'] > 1) {
        $_SESSION['cart'][$index]['quantity']--;
    }
}

// Remove item
if(isset($_POST['remove'])) {
    unset($_SESSION['cart'][$_POST['index']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Your Cart</title>

<style>
body {
    font-family: Arial;
    background: #f5f5f5;
}

.container {
    width: 80%;
    margin: auto;
}

.cart-item {
    background: white;
    padding: 15px;
    margin: 10px 0;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    display: flex;
    gap: 15px;
    align-items: center;
}
.cart-img {
    width: 90px;
    height: 90px;
    border-radius: 10px;
    object-fit: cover;
    background: #eee;
}
.cart-content {
    flex: 1;
}

button {
    padding: 5px 10px;
    margin: 5px;
}

.total {
    font-size: 20px;
    font-weight: bold;
}

</style>

</head>
<body>

<div class="container">

<h2>🛒 Your Cart</h2>

<a href="index.php">← Continue Shopping</a>

<?php
$total = 0;

if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0):

foreach($_SESSION['cart'] as $index => $item):

$subtotal = $item['price'] * $item['quantity'];
$total += $subtotal;
?>

<div class="cart-item">
    <?php if(!empty($item['image'])): ?>
        <img class="cart-img" src="images/<?= $item['image'] ?>" alt="<?= $item['name'] ?>">
    <?php else: ?>
        <div class="cart-img"></div>
    <?php endif; ?>

    <div class="cart-content">
        <h3><?= $item['name'] ?></h3>
        <p>Price: KES <?= $item['price'] ?></p>

    <form method="POST" style="display:inline;">
        <input type="hidden" name="index" value="<?= $index ?>">
        <button name="decrease">−</button>
    </form>

    <strong><?= $item['quantity'] ?></strong>

    <form method="POST" style="display:inline;">
        <input type="hidden" name="index" value="<?= $index ?>">
        <button name="increase">+</button>
    </form>

    <p>Subtotal: KES <?= $subtotal ?></p>

        <form method="POST">
            <input type="hidden" name="index" value="<?= $index ?>">
            <button name="remove" style="background:red; color:white;">Remove</button>
        </form>
    </div>
</div>

<?php endforeach; ?>

<p class="total">Total: KES <?= $total ?></p>

<?php else: ?>
<p>Your cart is empty</p>
<?php endif; ?>


</div>
<a href="checkout.php">
    <button style="padding:10px; background:blue; color:white;">
        Proceed to Checkout
    </button>
</a>

</body>
</html>
