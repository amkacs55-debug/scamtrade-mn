<?php
require_once 'includes/db.php';
require_login();
$u = current_user();

$mine = $pdo->prepare("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id WHERE l.user_id=? ORDER BY l.created_at DESC");
$mine->execute([$u['id']]); $mine = $mine->fetchAll();

$favs = $pdo->prepare("SELECT l.*, u.username FROM favorites f JOIN listings l ON l.id=f.listing_id JOIN users u ON u.id=l.user_id WHERE f.user_id=? ORDER BY f.created_at DESC");
$favs->execute([$u['id']]); $favs = $favs->fetchAll();

$tx = $pdo->prepare("SELECT t.*, l.title FROM transactions t JOIN listings l ON l.id=t.listing_id WHERE t.buyer_id=? ORDER BY t.created_at DESC");
$tx->execute([$u['id']]); $tx = $tx->fetchAll();

$reviews = $pdo->prepare("SELECT r.*, b.username AS buyer FROM reviews r JOIN users b ON b.id=r.buyer_id WHERE r.seller_id=? ORDER BY r.created_at DESC");
$reviews->execute([$u['id']]); $reviews = $reviews->fetchAll();

$pageTitle='Profile';
include 'includes/header.php';
?>
<div class="card" style="padding:30px;display:flex;gap:24px;align-items:center;flex-wrap:wrap">
  <div class="avatar" style="width:80px;height:80px;font-size:30px"><?= strtoupper(substr($u['username'],0,1)) ?></div>
  <div>
    <h1 style="font-size:28px"><?= e($u['username']) ?></h1>
    <p class="muted"><?= e($u['email']) ?></p>
  </div>
  <div style="margin-left:auto;display:flex;gap:10px">
    <a href="sell.php" class="btn-glow">+ New Listing</a>
    <a href="logout.php" class="btn-ghost">Logout</a>
  </div>
</div>

<section class="section">
  <h2 style="margin-bottom:16px">My <span class="accent">Listings</span></h2>
  <div class="grid">
    <?php if (!$mine): ?><p class="muted">You have no listings yet.</p>
    <?php else: foreach ($mine as $l) include 'includes/_card.php'; endif; ?>
  </div>
</section>

<section class="section">
  <h2 style="margin-bottom:16px">Favorites</h2>
  <div class="grid">
    <?php if (!$favs): ?><p class="muted">No favorites saved.</p>
    <?php else: foreach ($favs as $l) include 'includes/_card.php'; endif; ?>
  </div>
</section>

<section class="section">
  <h2 style="margin-bottom:16px">Purchase History</h2>
  <table class="table">
    <thead><tr><th>Listing</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
      <?php if (!$tx): ?><tr><td colspan="4" class="muted">No purchases yet.</td></tr>
      <?php else: foreach ($tx as $t): ?>
        <tr><td><?= e($t['title']) ?></td><td>$<?= number_format($t['amount'],2) ?></td><td><?= e($t['status']) ?></td><td><?= e($t['created_at']) ?></td></tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</section>

<section class="section">
  <h2 style="margin-bottom:16px">Seller Reviews</h2>
  <?php if (!$reviews): ?><p class="muted">No reviews yet.</p>
  <?php else: foreach ($reviews as $r): ?>
    <div class="card" style="padding:16px;margin-bottom:10px">
      <strong><?= e($r['buyer']) ?></strong> <span style="color:var(--neon-blue)"><?= str_repeat('★',(int)$r['rating']) ?></span>
      <p class="muted" style="margin-top:6px"><?= e($r['comment']) ?></p>
    </div>
  <?php endforeach; endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
