<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameVault - Marketplace</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: #0a0a0a;
            color: #00ff9d;
            font-family: 'Courier New', monospace;
            background-image: linear-gradient(rgba(0,0,0,0.9), rgba(0,0,0,0.9)), url('https://source.unsplash.com/random/1920x1080/?cyberpunk');
            background-size: cover;
        }
        header {
            background: rgba(0,0,0,0.9);
            border-bottom: 2px solid #00ff9d;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 28px; font-weight: bold; text-shadow: 0 0 15px #00ff9d; }
        nav a {
            color: #00ff9d;
            margin: 0 15px;
            text-decoration: none;
            transition: all 0.3s;
        }
        nav a:hover { text-shadow: 0 0 10px #00ff9d; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .card {
            background: #111;
            border: 1px solid #00ff9d;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            transition: all 0.3s;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 0 25px rgba(0, 255, 157, 0.4);
        }
        .neon-text { text-shadow: 0 0 10px #00ff9d; }
    </style>
</head>
<body>
<header>
    <div class="logo neon-text">GAMEVAULT</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="marketplace.php">Marketplace</a>
        <a href="profile.php">Profile</a>
        <a href="chat.php">Chat</a>
        <a href="seller.php">Sell</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?> 👾</h1>
    <p style="margin: 20px 0;">Cyberpunk Gaming Account Marketplace</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div class="card">
            <h2>Marketplace</h2>
            <p>Browse Standoff 2, MLBB, PUBG accounts</p>
            <a href="marketplace.php" style="color:#00ff9d;">→ Go to Marketplace</a>
        </div>
        <div class="card">
            <h2>Sell / Rent</h2>
            <p>List your account and earn money</p>
            <a href="seller.php" style="color:#00ff9d;">→ Start Selling</a>
        </div>
        <div class="card">
            <h2>Become Verified Seller</h2>
            <p>Get badge + more trust (66,000₮/month)</p>
            <a href="verified_seller.php" style="color:#00ff9d;">→ Upgrade Now</a>
        </div>
    </div>
</div>
</body>
</html>
