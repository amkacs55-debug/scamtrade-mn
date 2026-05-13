<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT l.*, u.username FROM listings l JOIN users u ON l.seller_id = u.id WHERE l.id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing || $listing['listing_type'] !== 'rent') {
    die("Rental not available");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Account</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:30px; }
        .box { max-width:700px; margin:auto; background:#111; padding:40px; border:2px solid #00ff9d; border-radius:12px; }
    </style>
</head>
<body>
<div class="box">
    <h1>RENT ACCOUNT</h1>
    <h2><?= htmlspecialchars($listing['title']) ?></h2>
    <p><strong>Game:</strong> <?= htmlspecialchars($listing['game']) ?></p>
    <p><strong>Rent Price:</strong> <?= number_format($listing['rent_price'] ?? $listing['price']) ?>₮</p>
    
    <form method="POST" action="">
        <label>Duration (days):</label>
        <select name="days">
            <option value="7">7 days</option>
            <option value="15">15 days</option>
            <option value="30">30 days</option>
        </select>
        <button type="submit" style="margin-top:20px; background:#00ff9d; color:black; padding:15px 30px;">CONFIRM RENT</button>
    </form>
    <p style="margin-top:20px; font-size:14px;">(Payment simulation - connect real gateway in production)</p>
</div>
</body>
</html>
