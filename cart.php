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
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&display=swap');

body {
    margin: 0;
    font-family: 'Manrope', Arial, sans-serif;
    background: #0b0b0b;
    color: #f5f5f5;
    overflow-x: hidden;
}

.video-bg {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    z-index: -2;
    background: #0b0b0b;
    filter: saturate(1) brightness(0.9);
}
.video-overlay {
    position: fixed;
    inset: 0;
    background:
        radial-gradient(1000px 500px at 12% 10%, rgba(255,255,255,0.08), transparent 60%),
        radial-gradient(900px 700px at 85% 20%, rgba(242,184,75,0.16), transparent 65%),
        linear-gradient(180deg, rgba(0,0,0,0.55), rgba(0,0,0,0.8));
    z-index: -1;
}

.container {
    width: min(920px, 88%);
    margin: 28px auto 40px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}
.page-header a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
}
.page-header a:hover { color: #f2b84b; }

.cart-grid {
    display: grid;
    gap: 14px;
}
.cart-item {
    background: rgba(24,24,24,0.85);
    padding: 12px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 16px 32px rgba(0,0,0,0.35);
    display: grid;
    grid-template-columns: 90px 1fr auto;
    gap: 12px;
    align-items: center;
}
.cart-img {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    object-fit: cover;
    background: rgba(255,255,255,0.08);
}
.cart-content {
    flex: 1;
}

button {
    padding: 6px 10px;
    margin: 4px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    color: #f5f5f5;
    cursor: pointer;
}

.total {
    font-size: 20px;
    font-weight: bold;
}

.qty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin: 6px 0;
}
.qty strong { min-width: 24px; text-align: center; }
.remove-btn {
    background: rgba(185,28,28,0.85);
    border-color: rgba(185,28,28,0.6);
    color: #fff;
}
.summary {
    margin-top: 18px;
    padding: 12px;
    border-radius: 14px;
    background: rgba(18,18,18,0.9);
    border: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.checkout-btn {
    padding: 10px 16px;
    background: #f2b84b;
    color: #111;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 10px 18px rgba(200,161,101,0.25);
}

@media (max-width: 720px) {
    .cart-item {
        grid-template-columns: 1fr;
        justify-items: start;
    }
    .cart-img {
        width: 100%;
        height: 160px;
    }
    .summary {
        flex-direction: column;
        align-items: stretch;
    }
}

</style>

</head>
<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="videos/cart2.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<div class="container">

<div class="page-header">
    <h2>Your Cart</h2>
    <a href="index.php">Continue Shopping</a>
</div>

<?php
$total = 0;

if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0):
?>
<div class="cart-grid">
<?php foreach($_SESSION['cart'] as $index => $item): ?>

<?php
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

        <div class="qty">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button name="decrease">-</button>
            </form>

            <strong><?= $item['quantity'] ?></strong>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button name="increase">+</button>
            </form>
        </div>

        <p>Subtotal: KES <?= $subtotal ?></p>
    </div>
    <div>
        <form method="POST">
            <input type="hidden" name="index" value="<?= $index ?>">
            <button class="remove-btn" name="remove">Remove</button>
        </form>
    </div>
</div>

<?php endforeach; ?>
</div>

<div class="summary">
    <div class="total">Total: KES <?= $total ?></div>
    <a href="checkout.php">
        <button class="checkout-btn">Proceed to Checkout</button>
    </a>
</div>

<?php else: ?>
<p>Your cart is empty</p>
<?php endif; ?>

</div>

<script>
    const bgVideo = document.querySelector('.video-bg');
    if (bgVideo) {
        bgVideo.playbackRate = 0.8;
    }
</script>

</body>
</html>

