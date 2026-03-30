<?php
require 'db.php';

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->query("INSERT INTO users(name,email,password) VALUES('$name','$email','$password')");

    echo "Account created! <a href='login.php'>Login</a>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&display=swap');

:root {
    --bg: #0c0c0c;
    --panel: #171717;
    --panel-2: #1d1d1d;
    --border: rgba(255,255,255,0.08);
    --text: #f5f5f5;
    --muted: #b3b3b3;
    --blue: #1e7ad7;
    --blue-2: #1662b0;
    --focus: rgba(30,122,215,0.35);
}
* { box-sizing: border-box; }
body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Manrope', sans-serif;
    color: var(--text);
    background: var(--bg);
    display: grid;
    place-items: center;
    overflow: hidden;
}
.video-bg {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
    filter: saturate(1) brightness(0.85);
}
.video-overlay {
    position: fixed;
    inset: 0;
    background:
        radial-gradient(800px 500px at 15% 10%, rgba(255,255,255,0.08), transparent 60%),
        radial-gradient(900px 600px at 85% 20%, rgba(30,122,215,0.18), transparent 65%),
        linear-gradient(180deg, rgba(0,0,0,0.55), rgba(0,0,0,0.8));
    z-index: -1;
}
.shell {
    width: min(420px, 92vw);
    padding: 22px 24px 20px;
    background: linear-gradient(180deg, var(--panel), #121212);
    border: 1px solid var(--border);
    border-radius: 22px;
    box-shadow:
        0 24px 50px rgba(0,0,0,0.55),
        inset 0 1px 0 rgba(255,255,255,0.03);
    position: relative;
}
.shell:before,
.shell:after {
    content: "";
    position: absolute;
    inset: -28px;
    border: 1px solid rgba(255,255,255,0.04);
    border-radius: 26px;
    pointer-events: none;
}
.shell:after {
    inset: -48px;
    border-radius: 30px;
    opacity: 0.5;
}
.close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,0.14);
    color: #fff;
    background: rgba(255,255,255,0.06);
    display: grid;
    place-items: center;
    text-decoration: none;
    font-size: 14px;
}
.tabs {
    display: inline-flex;
    gap: 6px;
    background: #111;
    border: 1px solid rgba(255,255,255,0.1);
    padding: 4px;
    border-radius: 999px;
    margin-bottom: 14px;
}
.tab {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
}
.tab.active {
    background: #1c1c1c;
    color: #fff;
    border: 1px solid rgba(255,255,255,0.08);
}
.title {
    font-size: 18px;
    margin: 6px 0 14px;
}
.field {
    margin: 10px 0;
    position: relative;
}
.row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
}
.field input {
    width: 100%;
    padding: 12px 12px 12px 36px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.08);
    background: #101010;
    color: var(--text);
    font-size: 13px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.4);
    transition: border-color 160ms ease, box-shadow 160ms ease;
}
.field input::placeholder { color: rgba(255,255,255,0.45); }
.field input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow:
        inset 0 2px 6px rgba(0,0,0,0.4),
        0 0 0 3px var(--focus);
}
.field .icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.5);
    font-size: 12px;
}
.primary {
    width: 100%;
    margin-top: 10px;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.06);
    background: linear-gradient(180deg, #f0f0f0, #d9d9d9);
    color: #111;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 14px 26px rgba(0,0,0,0.35);
    transition: transform 160ms ease, box-shadow 160ms ease;
}
.primary:hover { transform: translateY(-1px); }
.primary:active { transform: translateY(0); box-shadow: 0 10px 20px rgba(0,0,0,0.35); }

.divider {
    margin: 14px 0 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,0.45);
    font-size: 11px;
}
.divider:before, .divider:after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,0.08);
}
.socials {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.socials .social-btn {
    padding: 10px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.08);
    background: var(--panel-2);
    cursor: pointer;
    display: grid;
    place-items: center;
}
.socials .social-btn img {
    display: block;
    width: 18px;
    height: 18px;
    object-fit: contain;
    filter: brightness(1.2) contrast(1.1) drop-shadow(0 2px 6px rgba(0,0,0,0.4));
}
.fineprint {
    margin-top: 12px;
    font-size: 11px;
    color: rgba(255,255,255,0.45);
    text-align: center;
}
.fineprint a { color: #9cc3ff; text-decoration: none; }
</style>
</head>

<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="videos/login.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<div class="shell">
    <a class="close" href="login.php" aria-label="Close">×</a>
    <div class="tabs">
        <span class="tab active">Sign up</span>
        <a class="tab" href="login.php">Sign in</a>
    </div>

    <div class="title">Create an account</div>

    <form method="POST">
        <div class="row">
            <div class="field">
                <span class="icon">N</span>
                <input type="text" name="name" placeholder="Name" required>
            </div>
        </div>
        <div class="field">
            <span class="icon">@</span>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="field">
            <span class="icon">*</span>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button class="primary" name="register">Create an account</button>
    </form>

    <div class="divider">OR SIGN IN WITH</div>
    <div class="socials">
        <button class="social-btn" type="button" aria-label="Google">
            <img src="images/google.png" alt="Google">
        </button>
        <button class="social-btn" type="button" aria-label="Apple">
            <img src="images/apple-logo.png" alt="Apple">
        </button>
    </div>

    <div class="fineprint">
        By creating an account, you agree to our <a href="#">Terms</a> &amp; <a href="#">Service</a>
    </div>
</div>

<script>
    const bgVideo = document.querySelector('.video-bg');
    if (bgVideo) {
        bgVideo.playbackRate = 0.8;
    }
</script>

</body>
</html>
