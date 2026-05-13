<?php /* shared account card partial — expects $l with: id, title, game, rank_name, skin_count, price, image, views, username */ ?>
<div class="card">
  <div class="card-img">
    <span class="card-game"><?= e($l['game']) ?></span>
    <button class="fav-btn" data-id="<?= (int)$l['id'] ?>" aria-label="Favorite">♥</button>
    <img src="<?= e($l['image'] ?: 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800') ?>" alt="<?= e($l['title']) ?>">
  </div>
  <div class="card-body">
    <h3 class="card-title"><?= e($l['title']) ?></h3>
    <div class="meta">
      <span>★ <?= e($l['rank_name']) ?></span>
      <span><?= (int)$l['skin_count'] ?> skins</span>
    </div>
    <div class="meta">
      <span>by <?= e($l['username']) ?></span>
      <span>👁 <?= (int)$l['views'] ?></span>
    </div>
    <div class="card-foot">
      <div class="price">$<?= number_format($l['price'],2) ?></div>
      <a class="btn-view" href="product.php?id=<?= (int)$l['id'] ?>">View</a>
    </div>
  </div>
</div>
