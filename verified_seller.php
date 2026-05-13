<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO verified_sellers (user_id, expires_at) 
                          VALUES (?, NOW() + INTERVAL '30 days') 
                          ON CONFLICT (user_id) DO UPDATE SET expires_at = NOW() + INTERVAL '30 days'");
    $stmt->execute([$user_id]);
    $success = "You are now a Verified Seller for 30 days!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified Seller</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; text-align:center; padding:50px; }
        .box { background:#111; border:3px solid #ff00ff; max-width:600px; margin:auto; padding:40px; border-radius:15px; }
        button { background:#ff00ff; color:white; padding:20px 40px; font-size:18px; border:none; cursor:pointer; }
    </style>
</head>
<body>
<div class="box">
    <h1>⭐ BECOME VERIFIED SELLER</h1>
    <h2>66,000₮ / month</h2>
    <p>Get verified badge, higher trust, priority in marketplace</p>
    
    <?php if(isset($success)) echo "<p style='color:#00ff9d;font-size:18px;'>$success</p>"; ?>
    
    <form method="POST">
        <button type="submit">PAY 66,000₮ AND ACTIVATE</button>
    </form>
    <p style="margin-top:30px;">(In real project connect with payment gateway)</p>
</div>
</body>
</html>
