<?php
session_start();
require 'db.php';

if($_SESSION['user']['role'] != 'admin'){
    echo "Access denied";
    exit();
}

// ===== STATS =====

// Total Users
$users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Total Orders
$orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];

// Total Revenue (handle different orders table schemas)
$revenue = 0;
$cols = [];
$colResult = $conn->query("SHOW COLUMNS FROM orders");
if($colResult){
    while($row = $colResult->fetch_assoc()){
        $cols[] = $row['Field'];
    }
}
if(in_array('price', $cols) && in_array('quantity', $cols)){
    $revenue = $conn->query("SELECT SUM(price * quantity) as total FROM orders")->fetch_assoc()['total'] ?? 0;
} elseif(in_array('total', $cols)){
    $revenue = $conn->query("SELECT SUM(total) as total FROM orders")->fetch_assoc()['total'] ?? 0;
}

// DELETE PRODUCT
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin.php");
}

// ADD PRODUCT
if(isset($_POST['add'])){
    $name=$_POST['name'];
    $price=$_POST['price'];
    $category=$_POST['category'];
    $image=$_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], "images/".$image);

    $stmt = $conn->prepare("INSERT INTO products (name, price, category, image) VALUES (?,?,?,?)");
    $stmt->bind_param("sdss",$name,$price,$category,$image);
    $stmt->execute();
}

// FETCH PRODUCTS
$result = $conn->query("SELECT * FROM products");

// ===== INSIGHTS (LOCAL ONLY) =====
$openai_key = '';
$ai_error = '';
$ai_insight = '';
$ai_time = 0;
$local_insight = '';

function build_local_insights($users, $orders, $revenue, $topProducts){
    $lines = [];
    $lines[] = "Snapshot: {$users} users, {$orders} orders, KES {$revenue} revenue.";

    $avgOrder = ($orders > 0) ? round($revenue / $orders, 2) : 0;
    if($orders > 0){
        $lines[] = "Average order value: KES {$avgOrder}.";
    } else {
        $lines[] = "Average order value: not available (no orders yet).";
    }

    if(!empty($topProducts)){
        $top = $topProducts[0];
        $lines[] = "Top seller: {$top['pname']} ({$top['qty']} sold).";
        if(isset($top['revenue'])){
            $lines[] = "Top seller revenue: KES {$top['revenue']}.";
        }

        $top5Count = 0;
        foreach($topProducts as $p){
            $top5Count += (int)$p['qty'];
        }
        if($top5Count > 0){
            $lines[] = "Top 5 items total units: {$top5Count}.";
        }

        $lines[] = "Recommendation: restock top sellers and feature them on the homepage banner.";
        $lines[] = "Recommendation: create a bundle with the top seller to lift slower items.";
        $lines[] = "Recommendation: add a limited-time discount for the #2 or #3 product to balance demand.";
    } else {
        $lines[] = "Insight: item-level best-seller data is missing.";
        $lines[] = "Recommendation: upgrade the orders table to store product, price, and quantity.";
    }

    return "Local insights (offline):\n- " . implode("\n- ", $lines);
}

// Detect orders schema for best-seller stats
$orderCols = [];
$orderColResult = $conn->query("SHOW COLUMNS FROM orders");
if($orderColResult){
    while($row = $orderColResult->fetch_assoc()){
        $orderCols[] = $row['Field'];
    }
}
$hasProdName = in_array('product_name', $orderCols);
$hasName = in_array('name', $orderCols);
$hasQty = in_array('quantity', $orderCols);
$hasPrice = in_array('price', $orderCols);
$hasUserId = in_array('user_id', $orderCols);
$hasCreatedAt = in_array('created_at', $orderCols);

$topProducts = [];
if(($hasProdName || $hasName) && $hasQty){
    $nameCol = $hasProdName ? 'product_name' : 'name';
    $sqlTop = "SELECT $nameCol AS pname, SUM(quantity) AS qty";
    if($hasPrice){
        $sqlTop .= ", SUM(price * quantity) AS revenue";
    }
    $sqlTop .= " FROM orders GROUP BY $nameCol ORDER BY qty DESC LIMIT 5";
    $topResult = $conn->query($sqlTop);
    if($topResult){
        while($r = $topResult->fetch_assoc()){
            $topProducts[] = $r;
        }
    }
}

$didUpgrade = false;
if(isset($_POST['upgrade_orders_schema'])){
    $alter = [];
    if(!$hasProdName) $alter[] = "ADD COLUMN product_name VARCHAR(255) NULL";
    if(!$hasPrice) $alter[] = "ADD COLUMN price DECIMAL(10,2) NULL";
    if(!$hasQty) $alter[] = "ADD COLUMN quantity INT NULL";
    if(!$hasUserId) $alter[] = "ADD COLUMN user_id INT NULL";
    if(!$hasCreatedAt) $alter[] = "ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP";
    if(!empty($alter)){
        $sqlAlter = "ALTER TABLE orders " . implode(", ", $alter);
        if($conn->query($sqlAlter)){
            $didUpgrade = true;
            // reload columns after upgrade
            $orderCols = [];
            $orderColResult = $conn->query("SHOW COLUMNS FROM orders");
            if($orderColResult){
                while($row = $orderColResult->fetch_assoc()){
                    $orderCols[] = $row['Field'];
                }
            }
            $hasProdName = in_array('product_name', $orderCols);
            $hasName = in_array('name', $orderCols);
            $hasQty = in_array('quantity', $orderCols);
            $hasPrice = in_array('price', $orderCols);
            $hasUserId = in_array('user_id', $orderCols);
            $hasCreatedAt = in_array('created_at', $orderCols);
        } else {
            $ai_error = "Failed to upgrade orders table: " . $conn->error;
        }
    }
}

// Local insights only
$local_insight = build_local_insights($users, $orders, $revenue, $topProducts);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
body {
    font-family: Arial;
    margin:0;
    background: radial-gradient(1200px 600px at 10% 10%, #e9f7ff 0%, #c7d8ff 35%, #f6d6ff 70%, #fff3d1 100%);
}

.header {
    background: rgba(17,24,39,0.9);
    color:white;
    padding:15px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.back-btn {
    display: inline-block;
    padding: 8px 12px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    text-decoration: none;
    color: #fff;
    border-radius: 10px;
}

.stats {
    display:flex;
    gap:20px;
    padding:20px;
    flex-wrap: wrap;
}

.card {
    flex:1;
    min-width: 200px;
    padding:20px;
    border-radius:16px;
    text-align:center;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow:0 8px 24px rgba(0,0,0,0.12);
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

h2 {margin:0;}

.container {
    padding:20px;
}

form input, form button {
    padding:10px;
    margin:5px;
    border-radius:10px;
    border:1px solid rgba(0,0,0,0.1);
}
form button {
    background:#16a085;
    color:white;
    border:none;
    cursor:pointer;
}

table {
    width:100%;
    background: rgba(255,255,255,0.35);
    border-collapse:collapse;
    border-radius:12px;
    overflow:hidden;
}

table th, table td {
    padding:10px;
    border-bottom:1px solid rgba(0,0,0,0.08);
}
table th {
    background: rgba(255,255,255,0.6);
}
.insights {
    margin: 20px;
    padding: 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.insights h2 {
    margin-top: 0;
}
.insights .actions {
    margin-top: 10px;
}
.insights button {
    padding: 10px 14px;
    border-radius: 10px;
    border: none;
    background: #2563eb;
    color: white;
    cursor: pointer;
}
.insights .error {
    color: #b91c1c;
    background: rgba(255,255,255,0.7);
    padding: 10px;
    border-radius: 10px;
}
.insights .text {
    white-space: pre-line;
}
.charts {
    margin: 20px;
    padding: 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}
.chart-card {
    background: rgba(255,255,255,0.45);
    border-radius: 12px;
    padding: 12px;
    border: 1px solid rgba(0,0,0,0.08);
}
.chart-title {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #111827;
}
.chart-meta {
    font-size: 12px;
    color: #4b5563;
}
canvas {
    width: 100%;
    height: 200px;
}
</style>

</head>
<body>

<div class="header">
    <h1>📊 Admin Dashboard</h1>
    <a href="index.php" class="back-btn">Back</a>
</div>

<div class="stats">
    <div class="card">
        <h2><?= $users ?></h2>
        <p>Users</p>
    </div>

    <div class="card">
        <h2><?= $orders ?></h2>
        <p>Orders</p>
    </div>

    <div class="card">
        <h2>KES <?= $revenue ?></h2>
        <p>Revenue</p>
    </div>
</div>

<div class="insights">
    <h2>Insights: Best-Selling Products</h2>
    <?php if($local_insight): ?>
        <div class="text"><?= htmlspecialchars($local_insight) ?></div>
        <?php if(!$hasQty || (!$hasProdName && !$hasName)): ?>
            <p>To enable best-seller insights, we need to upgrade the <code>orders</code> table to store item-level details.</p>
            <form method="POST">
                <button name="upgrade_orders_schema">Upgrade Orders Table</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p>No insights yet. Click the button below to generate.</p>
    <?php endif; ?>

    <div class="actions">
        <form method="POST">
            <button name="generate_ai">Refresh Insights</button>
        </form>
    </div>
</div>

<div class="charts">
    <h2>Live Charts</h2>
    <p class="chart-meta">Auto-refreshes every 30 seconds.</p>
    <div class="charts-grid">
        <div class="chart-card">
            <p class="chart-title">Orders Over Time</p>
            <canvas id="lineChart" width="400" height="200"></canvas>
        </div>
        <div class="chart-card">
            <p class="chart-title" id="pieTitle">Mix</p>
            <canvas id="pieChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<div class="container">

<h2>Add Product</h2>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" placeholder="Product name" required>
<input type="number" name="price" placeholder="Price" required>
<input type="text" name="category" placeholder="Category" required>
<input type="file" name="image" required>
<button name="add">Add Product</button>
</form>

<hr>

<h2>Manage Products</h2>

<table>
<tr>
<th>Name</th>
<th>Price</th>
<th>Category</th>
<th>Action</th>
</tr>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['price'] ?></td>
<td><?= $row['category'] ?></td>
<td>
<a href="edit.php?id=<?= $row['id'] ?>">Edit</a> |
<a href="admin.php?delete=<?= $row['id'] ?>">Delete</a>
</td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>
<script>
(() => {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card) => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('is-hover');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('is-hover');
        });
        card.addEventListener('click', () => {
            card.classList.toggle('is-active');
        });
    });
})();

// Simple canvas charts (no external libraries)
const lineCtx = document.getElementById('lineChart')?.getContext('2d');
const pieCtx = document.getElementById('pieChart')?.getContext('2d');

function drawLineChart(ctx, labels, values){
    if(!ctx) return;
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    if(!values.length) return;

    const w = ctx.canvas.width;
    const h = ctx.canvas.height;
    const pad = 30;
    const maxVal = Math.max(...values, 1);

    ctx.strokeStyle = '#2563eb';
    ctx.lineWidth = 2;
    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad + (i * (w - pad * 2) / (values.length - 1 || 1));
        const y = h - pad - (v / maxVal) * (h - pad * 2);
        if(i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
    });
    ctx.stroke();

    // axes
    ctx.strokeStyle = '#9ca3af';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(pad, pad);
    ctx.lineTo(pad, h - pad);
    ctx.lineTo(w - pad, h - pad);
    ctx.stroke();
}

function drawPieChart(ctx, labels, values){
    if(!ctx) return;
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    if(!values.length) return;
    const total = values.reduce((a,b) => a + b, 0) || 1;
    let start = 0;
    const colors = ['#10b981','#2563eb','#f59e0b','#ef4444','#8b5cf6'];
    values.forEach((v, i) => {
        const slice = (v / total) * Math.PI * 2;
        ctx.beginPath();
        ctx.moveTo(100, 100);
        ctx.fillStyle = colors[i % colors.length];
        ctx.arc(100, 100, 80, start, start + slice);
        ctx.fill();
        start += slice;
    });
}

async function fetchCharts(){
    try {
        const res = await fetch('charts_data.php');
        const data = await res.json();
        drawLineChart(lineCtx, data.line.labels || [], data.line.values || []);
        drawPieChart(pieCtx, data.pie.labels || [], data.pie.values || []);
        const pieTitle = document.getElementById('pieTitle');
        if(pieTitle && data.pie.label) pieTitle.textContent = data.pie.label;
    } catch(e) {
        // ignore errors
    }
}

fetchCharts();
setInterval(fetchCharts, 30000);
</script>
