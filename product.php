<?php
require_once 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT l.*, u.username, u.id AS seller_id FROM listings l JOIN users u ON u.id=l.user_id WHERE l.id=?");
$stmt->execute([$id]);
$l = $stmt->fetch();
if (!$l) { http_response_code(404); echo 'Listing not found'; exit; }
$pdo->prepare("UPDATE listings SET views=views+1 WHERE id=?")->execute([$id]);

$similar = $pdo->prepare("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id WHERE l.game=? AND l.id<>? AND l.status='approved' ORDER BY l.views DESC LIMIT 4");
$similar->execute([$l['game'], $id]);
$similar = $similar->fetchAll();

$reviews = $pdo->prepare("SELECT r.*, u.username FROM reviews r JOIN users u ON u.id=r.buyer_id WHERE r.seller_id=? ORDER BY r.created_at DESC LIMIT 5");
$reviews->execute([$l['seller_id']]);
$reviews = $reviews->fetchAll();

$pageTitle = $l['title'];
include 'includes/header.php';
?>
<div class="product">
  <div>
    <div class="product-gallery">
      <img src="<?= e($l['image']) ?>" alt="<?= e($l['title']) ?>">
    </div>
    <div class="card" style="margin-top:18px;padding:20px">
      <h3 style="margin-bottom:10px">Description</h3>
      <p class="muted" style="line-height:1.7"><?= nl2br(e($l['description'])) ?></p>
    </div>
    <div class="card" style="margin-top:18px;padding:20px">
      <h3 style="margin-bottom:14px">Seller Reviews</h3>
      <?php if (!$reviews): ?><p class="muted">No reviews yet.</p>
      <?php else: foreach ($reviews as $r): ?>
        <div style="padding:10px 0;border-bottom:1px solid var(--border)">
          <strong><?= e($r['username']) ?></strong> <span style="color:var(--neon-blue)"><?= str_repeat('★',(int)$r['rating']) ?></span>
          <p class="muted" style="margin-top:4px"><?= e($r['comment']) ?></p>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <div>
    <div class="product-info">
      <span class="tag"><?= e($l['game']) ?></span>
      <span class="tag" style="background:rgba(34,211,238,.15);border-color:rgba(34,211,238,.4)"><?= e($l['rank_name']) ?></span>
      <h1 style="font-size:26px;margin-top:14px"><?= e($l['title']) ?></h1>
      <div class="big-price">$<?= number_format($l['price'],2) ?></div>
      <div class="meta"><span><?= (int)$l['skin_count'] ?> skins</span><span>👁 <?= (int)$l['views'] ?> views</span></div>
      <div class="seller-row">
        <div class="avatar"><?= strtoupper(substr($l['username'],0,1)) ?></div>
        <div>
          <div style="font-weight:700"><?= e($l['username']) ?></div>
          <div class="muted" style="font-size:12px">Verified seller</div>
        </div>
      </div>
      <a href="payment.php?listing=<?= (int)$l['id'] ?>" class="btn-primary" style="display:block;text-align:center;margin-bottom:10px;text-decoration:none">Buy Now — $<?= number_format($l['price'],2) ?></a>
      <a href="chat.php?to=<?= (int)$l['seller_id'] ?>&listing=<?= (int)$l['id'] ?>" class="btn-ghost" style="display:block;text-align:center;margin-bottom:8px">Chat with Seller</a>
      <button class="fav-btn" data-id="<?= (int)$l['id'] ?>" style="position:static;width:100%;height:42px;border-radius:10px">♥ Add to Favorites</button>
    </div>
  </div>
</div>

<section class="section">
  <h2 style="font-size:24px;margin-bottom:18px">Similar <span class="accent">Listings</span></h2>
  <div class="grid">
    <?php foreach ($similar as $l) include 'includes/_card.php'; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
