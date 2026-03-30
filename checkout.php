<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$message = '';

if(isset($_POST['pay'])){
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
        $message = "Cart is empty";
    } else {
        $user_id = $_SESSION['user']['id'];
        $cols = [];
        $colResult = $conn->query("SHOW COLUMNS FROM orders");
        if($colResult){
            while($row = $colResult->fetch_assoc()){
                $cols[] = $row['Field'];
            }
        }

        $hasProductName = in_array('product_name', $cols);
        $hasName = in_array('name', $cols);
        $hasPrice = in_array('price', $cols);
        $hasQty = in_array('quantity', $cols);
        $hasUserId = in_array('user_id', $cols);
        $hasTotal = in_array('total', $cols);
        $hasCustomerName = in_array('customer_name', $cols);

        $placed = false;

        if($hasPrice && $hasQty && ($hasProductName || $hasName)){
            $nameCol = $hasProductName ? 'product_name' : 'name';
            $colsSql = "$nameCol, price, quantity";
            $types = "sdi";
            if($hasUserId){
                $colsSql .= ", user_id";
                $types .= "i";
            }
            $stmt = $conn->prepare("INSERT INTO orders ($colsSql) VALUES (" . rtrim(str_repeat("?,", strlen($types)), ",") . ")");
            foreach($_SESSION['cart'] as $item){
                if($hasUserId){
                    $stmt->bind_param($types, $item['name'], $item['price'], $item['quantity'], $user_id);
                } else {
                    $stmt->bind_param($types, $item['name'], $item['price'], $item['quantity']);
                }
                $stmt->execute();
            }
            $placed = true;
        } elseif($hasTotal){
            $total = 0;
            foreach($_SESSION['cart'] as $item){
                $total += ($item['price'] * $item['quantity']);
            }
            $insertCols = ["total"];
            $types = "d";
            $values = [$total];

            if($hasCustomerName){
                $insertCols[] = "customer_name";
                $types .= "s";
                $values[] = $_SESSION['user']['name'];
            }
            if($hasUserId){
                $insertCols[] = "user_id";
                $types .= "i";
                $values[] = $user_id;
            }

            $placeholders = rtrim(str_repeat("?,", count($insertCols)), ",");
            $stmt = $conn->prepare("INSERT INTO orders (" . implode(",", $insertCols) . ") VALUES ($placeholders)");
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $placed = true;
        }

        if($placed){
            unset($_SESSION['cart']);
            $message = "Order placed successfully!";
        } else {
            $message = "Orders table schema is not supported. Please check your orders columns.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(1200px 600px at 10% 10%, #e9f7ff 0%, #c7d8ff 35%, #f6d6ff 70%, #fff3d1 100%);
}
.card {
    width: 360px;
    padding: 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 10px 26px rgba(0,0,0,0.14);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}
.card.is-hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 36px rgba(0,0,0,0.18);
    border-color: rgba(255,255,255,0.7);
}
.card.is-active {
    transform: translateY(-1px) scale(0.99);
    box-shadow: 0 12px 28px rgba(0,0,0,0.22);
    border-color: rgba(0,0,0,0.15);
}
.card h2 {
    margin: 0 0 12px 0;
}
.actions {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}
.actions a {
    flex: 1;
    text-align: center;
    padding: 8px 10px;
    border-radius: 10px;
    text-decoration: none;
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(0,0,0,0.1);
    color: #111;
}
.msg {
    margin: 10px 0 0 0;
    padding: 10px;
    border-radius: 10px;
    background: rgba(255,255,255,0.55);
    border: 1px solid rgba(0,0,0,0.08);
}
button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 10px;
    border: none;
    background: #16a085;
    color: white;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="card">
    <h2>Checkout</h2>
    <div class="actions">
        <a href="cart.php">Back to Cart</a>
        <a href="orders.php">Orders</a>
    </div>
    <form method="POST">
        <button name="pay">Place Order</button>
    </form>

    <?php if($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>
</div>

<script>
(() => {
    const card = document.querySelector('.card');
    if (!card) return;
    card.addEventListener('mouseenter', () => {
        card.classList.add('is-hover');
    });
    card.addEventListener('mouseleave', () => {
        card.classList.remove('is-hover');
    });
    card.addEventListener('click', () => {
        card.classList.toggle('is-active');
    });
})();
</script>

</body>
</html>
