<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT l.*, u.username FROM favorites f 
                      JOIN listings l ON f.listing_id = l.id 
                      JOIN users u ON l.seller_id = u.id 
                      WHERE f.user_id = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        .card { background:#111; border:1px solid #00ff9d; padding:15px; margin:15px 0; }
    </style>
</head>
<body>
<h1>Your Favorites</h1>
<?php foreach($favorites as $f): ?>
    <div class="card">
        <h3><?= htmlspecialchars($f['title']) ?></h3>
        <p><?= htmlspecialchars($f['game']) ?> - <?= number_format($f['price']) ?>₮</p>
        <a href="account_sell.php?id=<?= $f['id'] ?>" style="color:#00ff9d;">View</a>
    </div>
<?php endforeach; ?>

<?php if(empty($favorites)) echo "<p>No favorites yet.</p>"; ?>
</body>
</html>
