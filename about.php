<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>About Us</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&family=Playfair+Display:wght@500;600;700&display=swap');

:root {
    --ink: #f4f4f4;
    --deep: #0a0f0f;
    --teal: #1f8a8a;
    --gold: #f8d88b;
    --gold-2: #dab254;
    --muted: #b7c1c1;
    --card: rgba(255,255,255,0.06);
    --border: rgba(255,255,255,0.12);
}
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: 'Manrope', sans-serif;
    color: var(--ink);
    background:
        radial-gradient(900px 500px at 10% 10%, rgba(248,216,139,0.08), transparent 60%),
        radial-gradient(800px 600px at 90% 15%, rgba(31,138,138,0.12), transparent 65%),
        var(--deep);
}
.wrap {
    width: min(1150px, 92%);
    margin: 0 auto;
}
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 0;
}
.brand {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    letter-spacing: 0.5px;
}
.nav a {
    color: rgba(255,255,255,0.75);
    text-decoration: none;
    margin-left: 16px;
    font-size: 14px;
}
.nav a:hover { color: var(--gold); }

.hero {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 24px;
    align-items: center;
    padding: 30px 0 22px;
}
.hero-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 28px;
    box-shadow: 0 24px 50px rgba(0,0,0,0.4);
}
.hero h1 {
    margin: 0 0 12px 0;
    font-family: 'Playfair Display', serif;
    font-size: 42px;
    line-height: 1.1;
}
.hero p {
    margin: 0 0 18px 0;
    color: var(--muted);
}
.hero-cta {
    display: inline-flex;
    gap: 12px;
    align-items: center;
}
.btn {
    padding: 10px 16px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}
.btn.primary {
    background: linear-gradient(180deg, var(--gold), var(--gold-2));
    color: #1b1b1b;
    border: none;
}

.hero-art {
    position: relative;
    min-height: 320px;
    border-radius: 28px;
    background:
        radial-gradient(180px 180px at 20% 20%, rgba(248,216,139,0.5), transparent 60%),
        radial-gradient(260px 260px at 80% 30%, rgba(31,138,138,0.45), transparent 65%),
        linear-gradient(160deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
    border: 1px solid var(--border);
    box-shadow: 0 24px 50px rgba(0,0,0,0.4);
    overflow: hidden;
}
.hero-art img {
    position: absolute;
    right: 18px;
    bottom: -10px;
    width: 62%;
    border-radius: 16px;
    box-shadow: 0 18px 36px rgba(0,0,0,0.35);
}
.badge {
    position: absolute;
    top: 18px;
    left: 18px;
    background: rgba(0,0,0,0.45);
    border: 1px solid rgba(255,255,255,0.12);
    padding: 10px 12px;
    border-radius: 14px;
    font-size: 12px;
    color: var(--gold);
}

.section {
    margin: 36px 0;
}
.section h2 {
    font-family: 'Playfair Display', serif;
    margin: 0 0 12px 0;
}
.features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.feature {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 18px;
}
.feature h4 { margin: 0 0 8px 0; }
.feature p { margin: 0; color: var(--muted); font-size: 14px; }

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.stat {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
}
.stat strong {
    display: block;
    font-size: 22px;
    color: var(--gold);
}
.stat span { color: var(--muted); font-size: 12px; }

.team {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.person {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
}
.person img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 14px;
    margin-bottom: 10px;
}
.person small { color: var(--muted); }

.cta {
    background: linear-gradient(135deg, rgba(31,138,138,0.35), rgba(248,216,139,0.25));
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

@media (max-width: 900px) {
    .hero { grid-template-columns: 1fr; }
    .features { grid-template-columns: 1fr; }
    .stats { grid-template-columns: repeat(2, 1fr); }
    .team { grid-template-columns: 1fr; }
    .cta { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<body>

<div class="wrap">
    <header>
        <div class="brand">MyShop</div>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">Orders</a>
        </div>
    </header>

    <section class="hero">
        <div class="hero-card">
            <h1>Crafted care for every order, from shelf to doorstep</h1>
            <p>We are a small team building a refined inventory experience. Our focus is reliability, clarity, and thoughtful service that helps you shop with confidence.</p>
            <div class="hero-cta">
                <a class="btn primary" href="index.php">Browse Products</a>
                <a class="btn" href="cart.php">View Cart</a>
            </div>
        </div>
        <div class="hero-art">
            <div class="badge">Trusted by 1,200+ customers</div>
            <img src="images/laptop.jpg" alt="Team at work">
        </div>
    </section>

    <section class="section">
        <h2>Why customers choose us</h2>
        <div class="features">
            <div class="feature">
                <h4>Transparent pricing</h4>
                <p>No hidden fees. Clear product details and totals before you checkout.</p>
            </div>
            <div class="feature">
                <h4>Responsive support</h4>
                <p>Quick replies and real help when you need it most.</p>
            </div>
            <div class="feature">
                <h4>Quality-first sourcing</h4>
                <p>We curate products that meet consistent quality standards.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="stats">
            <div class="stat"><strong>1200+</strong><span>Orders delivered</span></div>
            <div class="stat"><strong>4.9/5</strong><span>Average rating</span></div>
            <div class="stat"><strong>24h</strong><span>Support response</span></div>
            <div class="stat"><strong>6 yrs</strong><span>Operating</span></div>
        </div>
    </section>

    <section class="section">
        <h2>Meet the team</h2>
        <div class="team">
            <div class="person">
                <img src="images/phone.jpg" alt="Operations lead">
                <h4>Grace M.</h4>
                <small>Operations</small>
            </div>
            <div class="person">
                <img src="images/headphones.jpg" alt="Customer success">
                <h4>Daniel K.</h4>
                <small>Customer Success</small>
            </div>
            <div class="person">
                <img src="images/computer.jpg" alt="Product curator">
                <h4>Amina S.</h4>
                <small>Product Curator</small>
            </div>
        </div>
    </section>

    <section class="section cta">
        <div>
            <h2>Ready to explore the catalog?</h2>
            <p>Discover curated essentials and bring clarity to your inventory.</p>
        </div>
        <a class="btn primary" href="index.php">Start Shopping</a>
    </section>
</div>

</body>
</html>
