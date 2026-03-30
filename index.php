<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// ADD TO CART
if(isset($_POST['add_to_cart'])){
    $id=$_POST['id'];
    $name=$_POST['name'];
    $price=$_POST['price'];
    $image=$_POST['image'] ?? '';

    if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];

    $found=false;

    foreach($_SESSION['cart'] as &$item){
        if($item['id']==$id){
            $item['quantity']++;
            $found=true;
            break;
        }
    }

    if(!$found){
        $_SESSION['cart'][]=[
            'id'=>$id,
            'name'=>$name,
            'price'=>$price,
            'image'=>$image,
            'quantity'=>1
        ];
    }

    $_SESSION['cart_notice'] = 1;
    header("Location:index.php?added=1");
    exit();
}

// FETCH PRODUCTS
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if($category){
    $sql .= " AND category=?";
    $params[]=$category;
    $types.="s";
}

if($search){
    $sql .= " AND name LIKE ?";
    $params[]="%$search%";
    $types.="s";
}

$stmt = $conn->prepare($sql);

if($params){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$show_notice = isset($_SESSION['cart_notice']);
if($show_notice){
    unset($_SESSION['cart_notice']);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>

<style>
body {
    margin:0;
    font-family:'Segoe UI', sans-serif;
    display:flex;
}

/* SIDEBAR */
.sidebar {
    width:240px;
    height:100vh;
    background:#111827;
    color:white;
    padding:20px;
}

.sidebar h2 {
    margin-bottom:20px;
}

.sidebar a {
    display:block;
    padding:12px;
    margin:6px 0;
    color:#d1d5db;
    text-decoration:none;
    border-radius:6px;
}

.sidebar a:hover {
    background:#1f2937;
    color:white;
}

/* MAIN */
.main {
    flex:1;
    background:#f9fafb;
    padding:20px;
}

/* TOPBAR */
.topbar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    gap:12px;
    flex-wrap:wrap;
}

.search-box {
    display:flex;
    gap:8px;
    align-items:center;
}

.search-box input {
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

.search-box button {
    padding:10px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:6px;
}

/* GRID */
.products {
    display:grid;
    grid-template-columns: repeat(auto-fill,minmax(230px,1fr));
    gap:20px;
}

/* CARD */
.card {
    background:white;
    border-radius:12px;
    padding:15px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    border: 1px solid transparent;
    cursor: pointer;
}

.card.is-hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 26px rgba(0,0,0,0.12);
    border-color: rgba(0,0,0,0.08);
}

.card.is-active {
    transform: translateY(-2px) scale(0.99);
    box-shadow: 0 10px 22px rgba(0,0,0,0.18);
    border-color: rgba(0,0,0,0.15);
}

.card img {
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:8px;
}

.price {
    font-weight:bold;
    margin:10px 0;
}

button {
    width:100%;
    padding:10px;
    border:none;
    background:#10b981;
    color:white;
    border-radius:6px;
}
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999;
}
.modal-backdrop.show {
    display: flex;
}
.modal {
    width: 360px;
    padding: 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.9);
    box-shadow: 0 16px 36px rgba(0,0,0,0.2);
    text-align: center;
}
.modal h3 {
    margin: 0 0 8px 0;
}
.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 14px;
}
.modal-actions a, .modal-actions button {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    border: none;
    text-decoration: none;
    cursor: pointer;
}
.modal-actions a {
    background: #10b981;
    color: white;
}
.modal-actions button {
    background: #111827;
    color: #fff;
}
</style>

</head>
<body>

<div class="sidebar">
    <h2>🛒 MyShop</h2>

    <a href="index.php">Dashboard</a>
    <a href="?category=Electronics">Electronics</a>
    <a href="?category=Fashion">Fashion</a>
    <a href="?category=Kids">Kids</a>

    <hr>

    <a href="cart.php">Cart</a>
    <a href="orders.php">Orders</a>

    <?php if($_SESSION['user']['role']=='admin'): ?>
        <a href="admin.php">Admin</a>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</div>

<div class="main">

<div class="topbar">
    <h2><?= $category ?: "All Products" ?></h2>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search..." value="<?= $search ?>">
        <button>Search</button>
    </form>
</div>

<div class="products">

<?php while($row=$result->fetch_assoc()): ?>
<div class="card">
    <img src="images/<?= $row['image'] ?>">
    <h3><?= $row['name'] ?></h3>
    <p class="price">KES <?= $row['price'] ?></p>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="name" value="<?= $row['name'] ?>">
        <input type="hidden" name="price" value="<?= $row['price'] ?>">
        <input type="hidden" name="image" value="<?= $row['image'] ?>">
        <button name="add_to_cart">Add to Cart</button>
    </form>
</div>
<?php endwhile; ?>

</div>

</div>

<div class="modal-backdrop" id="cartModal">
    <div class="modal">
        <h3>Added to cart</h3>
        <p>Your product was successfully added.</p>
        <div class="modal-actions">
            <a href="cart.php">Go to Cart</a>
            <button type="button" id="closeModal">Continue Shopping</button>
        </div>
    </div>
</div>

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
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            card.classList.toggle('is-active');
        });
    });

    const showNotice = <?= $show_notice ? 'true' : 'false' ?>;
    if(showNotice){
        const modal = document.getElementById('cartModal');
        const closeBtn = document.getElementById('closeModal');
        if(modal){
            modal.classList.add('show');
        }
        if(closeBtn){
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('show');
            });
        }
    }
})();
</script>

</body>
</html>
