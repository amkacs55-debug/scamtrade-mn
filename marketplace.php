<?php
$pageTitle='Marketplace';
require_once 'includes/db.php';
include 'includes/header.php';

$game = $_GET['game'] ?? '';
$rank = $_GET['rank'] ?? '';
$max  = (float)($_GET['max'] ?? 0);
$min  = (float)($_GET['min'] ?? 0);
$skins= (int)($_GET['skins'] ?? 0);
$q    = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['p'] ?? 1));
$per  = 12;

$where = ["status='approved'"]; $params = [];
if ($game) { $where[]='game = ?'; $params[]=$game; }
if ($rank) { $where[]='rank_name = ?'; $params[]=$rank; }
if ($max>0){ $where[]='price <= ?'; $params[]=$max; }
if ($min>0){ $where[]='price >= ?'; $params[]=$min; }
if ($skins>0){ $where[]='skin_count >= ?'; $params[]=$skins; }
if ($q){ $where[]='(title LIKE ? OR description LIKE ?)'; $params[]="%$q%"; $params[]="%$q%"; }
$wsql = 'WHERE '.implode(' AND ', $where);

$order = $sort==='popular' ? 'views DESC' : ($sort==='price_asc' ? 'price ASC' : ($sort==='price_desc' ? 'price DESC' : 'created_at DESC'));

$cnt = $pdo->prepare("SELECT COUNT(*) FROM listings $wsql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total/$per));
$off = ($page-1)*$per;

$sql = "SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id $wsql ORDER BY $order LIMIT $per OFFSET $off";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<h1 style="font-size:34px;margin-bottom:8px">Marketplace</h1>
<p class="muted" style="margin-bottom:22px"><?= $total ?> accounts available</p>

<div class="layout">
  <aside class="sidebar">
    <form id="filterForm" method="get">
      <input type="hidden" name="q" value="<?= e($q) ?>">
      <h3>Game</h3>
      <div class="field"><select name="game">
        <option value="">All games</option>
        <?php foreach (['Mobile Legends','PUBG Mobile','Standoff2'] as $g): ?>
          <option <?= $game===$g?'selected':'' ?>><?= $g ?></option>
        <?php endforeach; ?>
      </select></div>
      <h3>Rank</h3>
      <div class="field"><input name="rank" value="<?= e($rank) ?>" placeholder="e.g. Mythic"></div>
      <h3>Min Price</h3>
      <div class="field"><input type="number" name="min" value="<?= $min ?: '' ?>" placeholder="0"></div>
      <h3>Max Price</h3>
      <div class="field"><input type="number" name="max" value="<?= $max ?: '' ?>" placeholder="9999"></div>
      <h3>Min Skins</h3>
      <div class="field"><input type="number" name="skins" value="<?= $skins ?: '' ?>" placeholder="0"></div>
      <button class="btn-primary" type="submit">Apply Filters</button>
    </form>
  </aside>

  <div>
    <form class="toolbar" method="get">
      <?php foreach (['game','rank','min','max','skins'] as $k) if (!empty($_GET[$k])): ?>
        <input type="hidden" name="<?= $k ?>" value="<?= e($_GET[$k]) ?>">
      <?php endif; endforeach; ?>
      <input name="q" value="<?= e($q) ?>" placeholder="Search accounts, skins, ranks…">
      <select name="sort">
        <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Newest</option>
        <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Most popular</option>
        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Price ↑</option>
        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price ↓</option>
      </select>
      <button class="btn-glow" type="submit">Search</button>
    </form>

    <div class="grid">
      <?php if (!$items): ?>
        <p class="muted">No listings match your filters.</p>
      <?php else: foreach ($items as $l) include 'includes/_card.php'; endif; ?>
    </div>

    <?php if ($pages>1): ?>
    <div class="pagination">
      <?php for ($i=1;$i<=$pages;$i++):
        $qs = $_GET; $qs['p']=$i; $url='?'.http_build_query($qs); ?>
        <?php if ($i==$page): ?><span class="active"><?= $i ?></span>
        <?php else: ?><a href="<?= e($url) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="assets/js/marketplace.js"></script>
<?php include 'includes/footer.php'; ?>
