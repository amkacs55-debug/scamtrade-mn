<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT l.*, u.username FROM listings l JOIN users u ON l.seller_id = u.id WHERE l.id = ?");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    die("Listing not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($listing['title']) ?></title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        .detail { max-width:900px; margin:auto; background:#111; padding:30px; border:2px solid #00ff9d; border-radius:12px; }
    </style>
</head>
<body>
<div class="detail">
    <h1><?= htmlspecialchars($listing['title']) ?></h1>
    <p><strong>Game:</strong> <?= htmlspecialchars($listing['game']) ?></p>
    <p><strong>Level:</strong> <?= $listing['account_level'] ?></p>
    <p><strong>Price:</strong> <?= number_format($listing['price']) ?>₮</p>
    <p><strong>Seller:</strong> <?= htmlspecialchars($listing['username']) ?></p>
    <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
    
    <a href="#" style="background:#00ff9d;color:black;padding:15px 30px;text-decoration:none;display:inline-block;margin-top:20px;">BUY / CHAT WITH SELLER</a>
</div>
</body>
</html>
