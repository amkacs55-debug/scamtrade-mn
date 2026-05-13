<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$game = $_GET['game'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT l.*, u.username FROM listings l 
        JOIN users u ON l.seller_id = u.id 
        WHERE l.status = 'available'";
$params = [];

if ($game) {
    $sql .= " AND l.game = ?";
    $params[] = $game;
}
if ($search) {
    $sql .= " AND (l.title ILIKE ? OR l.description ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; }
        .card { background:#111; border:1px solid #00ff9d; margin:15px; padding:15px; border-radius:8px; }
        .neon { text-shadow:0 0 10px #00ff9d; }
        input, select { padding:10px; background:#1a1a1a; border:1px solid #00ff9d; color:white; width:100%; margin:10px 0; }
    </style>
</head>
<body>
<div style="padding:20px;">
    <h1 class="neon">MARKETPLACE</h1>
    
    <form method="GET" style="margin:20px 0;">
        <input type="text" name="search" placeholder="Search accounts..." value="<?=htmlspecialchars($search)?>">
        <select name="game">
            <option value="">All Games</option>
            <option value="Standoff 2" <?= $game=='Standoff 2'?'selected':'' ?>>Standoff 2</option>
            <option value="Mobile Legends" <?= $game=='Mobile Legends'?'selected':'' ?>>Mobile Legends</option>
            <option value="PUBG Mobile" <?= $game=='PUBG Mobile'?'selected':'' ?>>PUBG Mobile</option>
        </select>
        <button type="submit" style="background:#00ff9d;color:black;padding:10px;">Search</button>
    </form>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
        <?php foreach($listings as $l): ?>
        <div class="card">
            <h3><?=htmlspecialchars($l['title'])?></h3>
            <p><strong>Game:</strong> <?=htmlspecialchars($l['game'])?></p>
            <p><strong>Level:</strong> <?= $l['account_level'] ?></p>
            <p><strong>Price:</strong> <?= number_format($l['price']) ?>₮</p>
            <p>Seller: <?=htmlspecialchars($l['username'])?></p>
            <a href="account_sell.php?id=<?= $l['id'] ?>" style="color:#00ff9d;">View Details →</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
