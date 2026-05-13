<?php
$pageTitle = 'Home';
require_once 'includes/db.php';
include 'includes/header.php';

$featured = $pdo->query("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id WHERE l.status='approved' ORDER BY l.views DESC LIMIT 6")->fetchAll();
$trending = $pdo->query("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id WHERE l.status='approved' ORDER BY l.created_at DESC LIMIT 6")->fetchAll();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalListings = $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$totalSold = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status='completed'")->fetchColumn();
$totalRev = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status='completed'")->fetchColumn();

$topSellers = $pdo->query("SELECT u.username, u.id, COUNT(l.id) as lc FROM users u LEFT JOIN listings l ON l.user_id=u.id GROUP BY u.id ORDER BY lc DESC LIMIT 4")->fetchAll();
?>
<section class="hero">
  <h1>Own The <span class="accent">Endgame</span></h1>
  <p>The premium marketplace to buy and sell stacked gaming accounts for Mobile Legends, PUBG Mobile, and Standoff2. Verified sellers. Secure transfers. Esports-grade.</p>
  <div class="hero-cta">
    <a href="marketplace.php" class="btn-glow btn-lg">Browse Marketplace</a>
    <a href="sell.php" class="btn-ghost btn-lg">Sell Your Account</a>
  </div>
</section>

<section class="section">
  <div class="stats">
    <div class="stat-card"><div class="num"><?= number_format($totalUsers) ?>+</div><div class="label">Active Players</div></div>
    <div class="stat-card"><div class="num"><?= number_format($totalListings) ?></div><div class="label">Live Listings</div></div>
    <div class="stat-card"><div class="num"><?= number_format($totalSold) ?></div><div class="label">Accounts Sold</div></div>
    <div class="stat-card"><div class="num">$<?= number_format($totalRev) ?></div><div class="label">Volume Traded</div></div>
  </div>
</section>

<section class="section">
  <div class="section-header"><h2>Featured <span class="accent">Accounts</span></h2><a href="marketplace.php" class="btn-ghost">View all</a></div>
  <div class="grid">
    <?php foreach ($featured as $l): include 'includes/_card.php'; endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="section-header"><h2>Trending <span class="accent">Now</span></h2></div>
  <div class="grid">
    <?php foreach ($trending as $l): include 'includes/_card.php'; endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="section-header"><h2>Top <span class="accent">Sellers</span></h2></div>
  <div class="grid">
    <?php foreach ($topSellers as $s): ?>
      <div class="card" style="padding:22px;text-align:center">
        <div class="avatar" style="width:64px;height:64px;margin:0 auto 12px;font-size:22px"><?= strtoupper(substr($s['username'],0,1)) ?></div>
        <h3 style="font-size:16px"><?= e($s['username']) ?></h3>
        <p class="muted" style="margin-top:6px;font-size:13px"><?= (int)$s['lc'] ?> listings</p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
