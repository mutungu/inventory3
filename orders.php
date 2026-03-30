<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

$cols = [];
$colResult = $conn->query("SHOW COLUMNS FROM orders");
if($colResult){
    while($row = $colResult->fetch_assoc()){
        $cols[] = $row['Field'];
    }
}

$hasUserId = in_array('user_id', $cols);
$hasCustomerName = in_array('customer_name', $cols);
$hasProductName = in_array('product_name', $cols);
$hasName = in_array('name', $cols);
$hasPrice = in_array('price', $cols);
$hasQty = in_array('quantity', $cols);
$hasTotal = in_array('total', $cols);
$hasCreatedAt = in_array('created_at', $cols);

if($hasUserId){
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif($hasCustomerName){
    $stmt = $conn->prepare("SELECT * FROM orders WHERE customer_name=?");
    $stmt->bind_param("s", $_SESSION['user']['name']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM orders");
}

$columnsToShow = [];
if($hasPrice && $hasQty && ($hasProductName || $hasName)){
    $columnsToShow[] = ['label' => 'Product', 'key' => $hasProductName ? 'product_name' : 'name'];
    $columnsToShow[] = ['label' => 'Price', 'key' => 'price'];
    $columnsToShow[] = ['label' => 'Quantity', 'key' => 'quantity'];
    if($hasCreatedAt) $columnsToShow[] = ['label' => 'Date', 'key' => 'created_at'];
} elseif($hasTotal){
    $columnsToShow[] = ['label' => 'Total', 'key' => 'total'];
    if($hasCustomerName) $columnsToShow[] = ['label' => 'Customer', 'key' => 'customer_name'];
    if($hasCreatedAt) $columnsToShow[] = ['label' => 'Date', 'key' => 'created_at'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Your Orders</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 30px;
    background: radial-gradient(1200px 600px at 10% 10%, #e9f7ff 0%, #c7d8ff 35%, #f6d6ff 70%, #fff3d1 100%);
}
.back-btn {
    display: inline-block;
    margin: 0 0 16px 0;
    padding: 8px 12px;
    background: rgba(255,255,255,0.5);
    border: 1px solid rgba(255,255,255,0.6);
    text-decoration: none;
    color: #111;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.card {
    padding: 16px;
    border-radius: 16px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}
.card.is-hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 32px rgba(0,0,0,0.18);
    border-color: rgba(255,255,255,0.7);
}
.card.is-active {
    transform: translateY(-1px) scale(0.99);
    box-shadow: 0 10px 26px rgba(0,0,0,0.22);
    border-color: rgba(0,0,0,0.15);
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: rgba(255,255,255,0.35);
    border-radius: 12px;
    overflow: hidden;
}
th, td {
    text-align: left;
    padding: 10px;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}
th {
    background: rgba(255,255,255,0.6);
}
</style>
</head>
<body>

<a href="index.php" class="back-btn">Back</a>

<div class="card">
    <h2>Your Orders</h2>

    <table>
        <tr>
            <?php if(!empty($columnsToShow)): ?>
                <?php foreach($columnsToShow as $col): ?>
                    <th><?= $col['label'] ?></th>
                <?php endforeach; ?>
            <?php else: ?>
                <th>Orders</th>
            <?php endif; ?>
        </tr>

        <?php if($result): ?>
            <?php while($row=$result->fetch_assoc()): ?>
            <tr>
                <?php if(!empty($columnsToShow)): ?>
                    <?php foreach($columnsToShow as $col): ?>
                        <td><?= $row[$col['key']] ?? '' ?></td>
                    <?php endforeach; ?>
                <?php else: ?>
                    <td>No supported order columns found.</td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
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
