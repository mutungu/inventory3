<?php
session_start();
require 'db.php';

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){
            $_SESSION['user'] = $user;
            header("Location: index.php");
        } else {
            echo "Wrong password!";
        }
    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
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
    width: min(380px, 92vw);
    padding: 28px 26px 22px;
    background: linear-gradient(180deg, var(--panel), #121212);
    border: 1px solid var(--border);
    border-radius: 18px;
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
    border-radius: 24px;
    pointer-events: none;
}
.shell:after {
    inset: -48px;
    border-radius: 28px;
    opacity: 0.5;
}
.logo {
    width: 52px;
    height: 52px;
    margin: 2px auto 10px;
    border-radius: 14px;
    background: #101010;
    border: 1px solid var(--border);
    display: grid;
    place-items: center;
    box-shadow: 0 10px 18px rgba(0,0,0,0.4);
}
.logo span {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 3px solid #88b7ff;
    border-right-color: transparent;
    display: block;
}
.title {
    text-align: center;
    font-size: 20px;
    margin: 6px 0 4px;
}
.subtitle {
    text-align: center;
    color: var(--muted);
    font-size: 12px;
    margin-bottom: 16px;
}
.subtitle a { color: #8fc0ff; text-decoration: none; }

.field {
    margin: 10px 0;
    position: relative;
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
    margin-top: 8px;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.06);
    background: linear-gradient(180deg, var(--blue), var(--blue-2));
    color: white;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 14px 26px rgba(30,122,215,0.35);
    transition: transform 160ms ease, box-shadow 160ms ease;
}
.primary:hover { transform: translateY(-1px); }
.primary:active { transform: translateY(0); box-shadow: 0 10px 20px rgba(30,122,215,0.35); }

.divider {
    margin: 16px 0 12px;
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
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
}
.socials .social-btn {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.08);
    background: #202020;
    color: #e5e5e5;
    cursor: pointer;
    font-size: 12px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.03);
    display: grid;
    place-items: center;
}
.socials .social-btn img {
    display: block;
    width: 22px;
    height: 22px;
    object-fit: contain;
    filter: brightness(1.25) contrast(1.1) drop-shadow(0 2px 6px rgba(0,0,0,0.4));
}
</style>
</head>
<body>

<video class="video-bg" autoplay muted loop playsinline>
    <source src="videos/login.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<div class="shell">
    <div class="logo"><span></span></div>
    <div class="title">Welcome Back</div>
    <div class="subtitle">Don't have an account yet? <a href="register.php">Sign up</a></div>

    <form method="POST">
        <div class="field">
            <span class="icon">✉</span>
            <input type="email" name="email" placeholder="email address" required>
        </div>
        <div class="field">
            <span class="icon">🔒</span>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button class="primary" name="login">Login</button>
    </form>

    <div class="divider">OR</div>
    <div class="socials">
        <button class="social-btn" type="button" aria-label="Apple">
            <img src="images/apple-logo.png" alt="Apple">
        </button>
        <button class="social-btn" type="button" aria-label="Google">
            <img src="images/google.png" alt="Google">
        </button>
        <button class="social-btn" type="button" aria-label="Instagram">
            <img src="images/instagram.png" alt="Instagram">
        </button>
        <button class="social-btn" type="button" aria-label="Twitter">
            <img src="images/twitter.png" alt="Twitter">
        </button>
        <button class="social-btn" type="button" aria-label="Facebook">
            <img src="images/facebook.png" alt="Facebook">
        </button>
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
