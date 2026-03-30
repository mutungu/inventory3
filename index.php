<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
 
// Quick order (dropdown)
if(isset($_POST['quick_order'])){
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);
    if($qty < 1) $qty = 1;

    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();

    if($p){
        if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];
        $found=false;
        foreach($_SESSION['cart'] as &$item){
            if($item['id']==$p['id']){
                $item['quantity'] += $qty;
                $found=true;
                break;
            }
        }
        if(!$found){
            $_SESSION['cart'][]=[
                'id'=>$p['id'],
                'name'=>$p['name'],
                'price'=>$p['price'],
                'image'=>$p['image'] ?? '',
                'quantity'=>$qty
            ];
        }
        $_SESSION['cart_notice'] = 1;
        header("Location: cart.php");
        exit();
    }
}

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

// Categories for dropdown
$categoryOptions = [];
$catResult = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category");
if($catResult){
    while($c = $catResult->fetch_assoc()){
        $categoryOptions[] = $c['category'];
    }
}

// Products for quick order dropdown
$quickProducts = $conn->query("SELECT id, name, price FROM products ORDER BY name");

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
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&family=Playfair+Display:wght@500;600;700&display=swap');

:root {
    --ink: #f5f5f5;
    --stone: #0b0b0b;
    --cream: #0f0f0f;
    --gold: #f2b84b;
    --gold-dark: #d79a2b;
    --deep: #0d0d0d;
    --muted: #b3b3b3;
    --card: rgba(24,24,24,0.85);
    --border: rgba(255,255,255,0.08);
    --glow: rgba(242,184,75,0.18);
}
body {
    margin:0;
    font-family:'Manrope', sans-serif;
    color: var(--ink);
    background:
        radial-gradient(1000px 500px at 12% 10%, rgba(255,255,255,0.04), transparent 60%),
        radial-gradient(900px 700px at 85% 20%, rgba(242,184,75,0.08), transparent 65%),
        linear-gradient(180deg, var(--stone), var(--cream));
}
.layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
}

/* SIDEBAR */
.sidebar {
    width:260px;
    height:100%;
    background: var(--deep);
    color: #f5efe4;
    padding:26px 22px;
    position: sticky;
    top: 0;
}

.sidebar h2 {
    margin-bottom:20px;
    font-family:'Playfair Display', serif;
    font-weight:600;
    letter-spacing:0.5px;
}

.sidebar a {
    display:block;
    padding:12px;
    margin:6px 0;
    color:rgba(255,255,255,0.78);
    text-decoration:none;
    border-radius:10px;
    border:1px solid transparent;
    transition: all 180ms ease;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.08);
    color:#fff;
    border-color: rgba(255,255,255,0.12);
}

/* MAIN */
.main {
    flex:1;
    padding:28px 32px 40px 32px;
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
.topbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.about-link {
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    font-size: 13px;
    transition: all 180ms ease;
}
.about-link:hover {
    color: #111;
    background: var(--gold);
    border-color: var(--gold);
    box-shadow: 0 8px 18px rgba(242,184,75,0.25);
}

.hero {
    padding: 24px;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(20,20,20,0.92), rgba(16,16,16,0.6));
    border: 1px solid var(--border);
    box-shadow: 0 20px 40px rgba(0,0,0,0.35);
    margin-bottom: 22px;
}
.hero h1 {
    margin: 0 0 8px 0;
    font-family:'Playfair Display', serif;
    font-size: 32px;
}
.hero p {
    margin: 0;
    color: var(--muted);
    max-width: 720px;
}

.search-box {
    display:flex;
    gap:8px;
    align-items:center;
}

.search-box input {
    padding:10px;
    border-radius:10px;
    border:1px solid var(--border);
    background: rgba(20,20,20,0.9);
    color: #f5f5f5;
}

.search-box button {
    padding:10px;
    background: #1a1a1a;
    color:white;
    border:none;
    border-radius:10px;
}
.filter-box select {
    padding:10px;
    border-radius:10px;
    border:1px solid var(--border);
    background: rgba(20,20,20,0.9);
    color: #f5f5f5;
}

/* GRID */
.products {
    display:grid;
    grid-template-columns: repeat(auto-fill,minmax(230px,1fr));
    gap:18px;
}

/* CARD */
.card {
    background: var(--card);
    border-radius:16px;
    padding:16px;
    box-shadow:0 14px 30px rgba(0,0,0,0.35);
    transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    border: 1px solid var(--border);
    cursor: pointer;
    transform-style: preserve-3d;
    will-change: transform;
}

.card.is-hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 36px rgba(0,0,0,0.12);
    border-color: rgba(0,0,0,0.12);
}

.card.is-active {
    transform: translateY(-2px) scale(0.99);
    box-shadow: 0 14px 28px rgba(0,0,0,0.16);
    border-color: rgba(0,0,0,0.18);
}

.card img {
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:12px;
    transition: transform 220ms ease;
}

.price {
    font-weight:bold;
    margin:10px 0;
    color: var(--gold);
}

button {
    width:100%;
    padding:10px;
    border:none;
    background: var(--gold);
    color:#111;
    border-radius:10px;
    box-shadow: 0 10px 18px rgba(200,161,101,0.25);
}
@keyframes floaty {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-12px); }
    100% { transform: translateY(0px); }
}
.card.floaty {
    animation: floaty 5.5s ease-in-out infinite;
}
.card.floaty:nth-child(2n){
    animation-duration: 6.2s;
    animation-delay: 0.6s;
}
.card.floaty:nth-child(3n){
    animation-duration: 6.8s;
    animation-delay: 1s;
}
.footer {
    margin: 0;
    padding: 24px;
    border-radius: 18px;
    background: rgba(18,18,18,0.9);
    border: 1px solid var(--border);
    box-shadow: 0 16px 32px rgba(0,0,0,0.35);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}
.page-footer {
    padding: 0 0 32px 0;
}
.footer h4 {
    margin: 0 0 8px 0;
    font-family:'Playfair Display', serif;
}
.footer a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    display: inline-block;
    margin: 4px 0;
}
.footer .social {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 6px;
}
.footer .social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.18);
    background: rgba(255,255,255,0.06);
    transition: all 180ms ease;
}
.footer .social img {
    width: 16px;
    height: 16px;
    object-fit: contain;
    filter: brightness(1.2) contrast(1.1);
}
.footer .social a:hover {
    border-color: var(--gold);
    box-shadow: 0 8px 18px rgba(242,184,75,0.25);
}
.footer a:hover {
    color: var(--gold-dark);
}
.footer .brand {
    color: rgba(255,255,255,0.7);
    font-size: 13px;
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

<div class="layout">
<div class="sidebar">
    <h2>🛒 MyShop</h2>

    <a href="index.php">Dashboard</a>
    <a href="index.php">Products</a>
    <a href="about.php">About</a>
    <a href="#quick-order">Place Order</a>

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
    <div class="topbar-left">
        <a class="about-link" href="about.php">About</a>
        <h2><?= $category ?: "" ?></h2>
    </div>

    <form method="GET" class="search-box">
        <div class="filter-box">
            <select name="category">
                <option value="">All Products</option>
                <?php foreach($categoryOptions as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="text" name="search" placeholder="Search..." value="<?= $search ?>">
        <button>Search</button>
    </form>
</div>

<div class="hero">
    <h1>Crafted Goods, Curated Inventory</h1>
    <p>Explore your catalog with a refined view—filter by product type, search fast, and move items to the cart with intention.</p>
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
</div>

<div class="page-footer">
    <div class="footer">
        <div>
            <h4>Contact</h4>
            <div class="brand">Email: support@myshop.com</div>
            <div class="brand">Phone: +254 700 000 000</div>
            <div class="brand">Nairobi, Kenya</div>
        </div>
        <div>
            <h4>Company</h4>
            <a href="#">About</a><br>
            <a href="#">Sourcing</a><br>
            <a href="#">Careers</a>
        </div>
        <div>
            <h4>Social</h4>
            <div class="social">
                <a href="https://www.instagram.com" aria-label="Instagram" title="Instagram">
                    <img src="images/instagram.png" alt="Instagram">
                </a>
                <a href="https://www.facebook.com" aria-label="Facebook" title="Facebook">
                    <img src="images/facebook.png" alt="Facebook">
                </a>
                <a href="https://www.twitter.com" aria-label="Twitter" title="Twitter">
                    <img src="images/twitter.png" alt="Twitter">
                </a>
            </div>
        </div>
        <div>
            <h4>Legal</h4>
            <a href="#">Privacy Policy</a><br>
            <a href="#">Terms</a><br>
            <a href="#">Returns</a>
        </div>
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
        card.classList.add('floaty');
        let raf = null;
        const onMove = (e) => {
            if(raf) return;
            raf = requestAnimationFrame(() => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const midX = rect.width / 2;
                const midY = rect.height / 2;
                const rotateX = ((y - midY) / midY) * -12;
                const rotateY = ((x - midX) / midX) * 12;
                card.style.transform = `translateY(-8px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                const img = card.querySelector('img');
                if(img){
                    img.style.transform = `translateY(-4px) scale(1.05)`;
                }
                raf = null;
            });
        };
        card.addEventListener('mouseenter', () => {
            card.classList.add('is-hover');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('is-hover');
            card.style.transform = '';
            const img = card.querySelector('img');
            if(img){
                img.style.transform = '';
            }
        });
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            card.classList.toggle('is-active');
        });
        card.addEventListener('mousemove', onMove);
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
