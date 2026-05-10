<?php
require_once 'config.php';

$game = $_GET['game'] ?? 'all';
$type = $_GET['type'] ?? 'all';
$search = $_GET['q'] ?? '';

$filters = 'status=eq.available&order=created_at.desc';
if ($game !== 'all') $filters .= '&game=eq.' . urlencode($game);
if ($type !== 'all') $filters .= '&type=eq.' . urlencode($type);

$result = sb_get('accounts', $filters);
$accounts = $result['data'] ?? [];

// Search filter (client-side баазад)
if ($search) {
    $accounts = array_filter($accounts, function($a) use ($search) {
        return stripos($a['title'], $search) !== false || stripos($a['description'], $search) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SITE_NAME ?> — Account Shop</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Noto+Sans+Mongolian&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #050810;
  --bg2: #0a0f1e;
  --card: #0d1428;
  --border: #1a2545;
  --accent: #00d4ff;
  --accent2: #ff6b35;
  --ml: #1e90ff;
  --pubg: #f5a623;
  --text: #e8eaf6;
  --muted: #6b7a99;
  --success: #00e676;
  --danger: #ff5252;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
}

/* ---- NAV ---- */
nav {
  background: rgba(10,15,30,0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  padding: 0 2rem;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 100;
}
.logo {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.6rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  text-decoration: none;
}
.nav-links { display: flex; gap: 1.5rem; align-items: center; }
.nav-links a {
  color: var(--muted);
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}
.nav-links a:hover { color: var(--accent); }
.btn {
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
  text-decoration: none;
  display: inline-block;
  font-family: 'Inter', sans-serif;
}
.btn-primary {
  background: linear-gradient(135deg, var(--accent), #0099cc);
  color: #000;
}
.btn-primary:hover { opacity: 0.85; transform: translateY(-1px); }
.btn-outline {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text);
}
.btn-outline:hover { border-color: var(--accent); color: var(--accent); }

/* ---- HERO ---- */
.hero {
  text-align: center;
  padding: 4rem 2rem 3rem;
  background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(0,212,255,0.12), transparent);
}
.hero h1 {
  font-family: 'Rajdhani', sans-serif;
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: 1rem;
}
.hero h1 span { color: var(--accent); }
.hero p { color: var(--muted); font-size: 1rem; max-width: 500px; margin: 0 auto 2rem; }

/* ---- FILTER BAR ---- */
.filter-bar {
  max-width: 1200px;
  margin: 0 auto 2rem;
  padding: 0 1.5rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
}
.filter-group { display: flex; gap: 0.5rem; }
.filter-btn {
  padding: 0.45rem 1rem;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--muted);
  transition: all 0.2s;
}
.filter-btn.active, .filter-btn:hover { border-color: var(--accent); color: var(--accent); background: rgba(0,212,255,0.08); }
.filter-btn.ml.active { border-color: var(--ml); color: var(--ml); background: rgba(30,144,255,0.1); }
.filter-btn.pubg.active { border-color: var(--pubg); color: var(--pubg); background: rgba(245,166,35,0.1); }
.search-input {
  margin-left: auto;
  background: var(--card);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 0.45rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  outline: none;
  width: 220px;
  transition: border-color 0.2s;
  font-family: 'Inter', sans-serif;
}
.search-input:focus { border-color: var(--accent); }

/* ---- GRID ---- */
.grid {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem 4rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* ---- CARD ---- */
.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.25s;
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  display: block;
  position: relative;
}
.card:hover {
  border-color: var(--accent);
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0,212,255,0.12);
}
.card.pubg-card:hover { border-color: var(--pubg); box-shadow: 0 8px 32px rgba(245,166,35,0.12); }

.card-img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  background: linear-gradient(135deg, #0d1428, #1a2545);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  position: relative;
}
.card-img img { width: 100%; height: 100%; object-fit: cover; }

.game-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
  font-family: 'Rajdhani', sans-serif;
  letter-spacing: 0.5px;
}
.game-badge.ml { background: rgba(30,144,255,0.85); color: #fff; }
.game-badge.pubg { background: rgba(245,166,35,0.9); color: #000; }

.type-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 700;
}
.type-badge.sale { background: rgba(0,230,118,0.15); color: var(--success); border: 1px solid var(--success); }
.type-badge.rent { background: rgba(255,107,53,0.15); color: var(--accent2); border: 1px solid var(--accent2); }
.type-badge.both { background: rgba(0,212,255,0.1); color: var(--accent); border: 1px solid var(--accent); }

.card-body { padding: 1rem 1.2rem 1.2rem; }
.card-title {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-meta {
  display: flex;
  gap: 0.8rem;
  font-size: 0.78rem;
  color: var(--muted);
  margin-bottom: 0.8rem;
}
.card-meta span { display: flex; align-items: center; gap: 3px; }
.card-price {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.price-buy {
  font-family: 'Rajdhani', sans-serif;
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--success);
}
.price-rent {
  font-size: 0.78rem;
  color: var(--accent2);
}

/* ---- EMPTY ---- */
.empty {
  grid-column: 1/-1;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--muted);
}
.empty-icon { font-size: 4rem; margin-bottom: 1rem; }

/* ---- FOOTER ---- */
footer {
  border-top: 1px solid var(--border);
  text-align: center;
  padding: 1.5rem;
  color: var(--muted);
  font-size: 0.8rem;
}
</style>
</head>
<body>

<nav>
  <a href="index.php" class="logo">⚔ ML&PUBG Shop</a>
  <div class="nav-links">
    <?php if (is_logged_in()): ?>
      <a href="dashboard_user.php">Миний захиалга</a>
      <?php if (is_admin()): ?>
        <a href="dashboard_admin.php">Admin</a>
      <?php endif; ?>
      <a href="logout.php">Гарах</a>
      <span style="color:var(--accent);font-size:0.85rem">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
    <?php else: ?>
      <a href="login.php">Нэвтрэх</a>
      <a href="register.php" class="btn btn-primary">Бүртгүүлэх</a>
    <?php endif; ?>
  </div>
</nav>

<div class="hero">
  <h1>Mobile Legends & <span>PUBG Mobile</span><br>Account Дэлгүүр</h1>
  <p>Найдвартай, хурдан, аюулгүй. Account худалдаж авах эсвэл түрээслэх.</p>
</div>

<div class="filter-bar">
  <div class="filter-group">
    <a href="?game=all&type=<?=$type?>" class="filter-btn <?= $game==='all'?'active':'' ?>">🎮 Бүгд</a>
    <a href="?game=mlbb&type=<?=$type?>" class="filter-btn ml <?= $game==='mlbb'?'active ml':'' ?>">⚔ ML</a>
    <a href="?game=pubg&type=<?=$type?>" class="filter-btn pubg <?= $game==='pubg'?'active pubg':'' ?>">🔫 PUBG</a>
  </div>
  <div class="filter-group">
    <a href="?game=<?=$game?>&type=all" class="filter-btn <?= $type==='all'?'active':'' ?>">Бүгд</a>
    <a href="?game=<?=$game?>&type=sale" class="filter-btn <?= $type==='sale'?'active':'' ?>">Зарах</a>
    <a href="?game=<?=$game?>&type=rent" class="filter-btn <?= $type==='rent'?'active':'' ?>">Түрээс</a>
  </div>
  <input type="text" class="search-input" placeholder="🔍 Хайх..." value="<?= htmlspecialchars($search) ?>"
    onkeyup="filterSearch(this.value)">
</div>

<div class="grid" id="accountGrid">
<?php if (empty($accounts)): ?>
  <div class="empty">
    <div class="empty-icon">🎮</div>
    <p>Одоогоор зарах account байхгүй байна.</p>
  </div>
<?php else: ?>
  <?php foreach ($accounts as $acc): ?>
  <?php
    $gameClass = $acc['game'] === 'mlbb' ? 'ml' : 'pubg';
    $gameLabel = $acc['game'] === 'mlbb' ? 'Mobile Legends' : 'PUBG Mobile';
    $emoji = $acc['game'] === 'mlbb' ? '⚔️' : '🔫';
    $typeLabel = ['sale'=>'Зарах','rent'=>'Түрээс','both'=>'Зарах/Түрээс'][$acc['type']] ?? $acc['type'];
    $img = !empty($acc['images']) ? $acc['images'][0] : '';
  ?>
  <a href="account_detail.php?id=<?= $acc['id'] ?>" class="card <?= $acc['game']==='pubg'?'pubg-card':'' ?>" data-title="<?= htmlspecialchars(strtolower($acc['title'])) ?>">
    <div class="card-img">
      <?php if ($img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($acc['title']) ?>">
      <?php else: ?>
        <span><?= $emoji ?></span>
      <?php endif; ?>
      <span class="game-badge <?= $gameClass ?>"><?= $gameLabel ?></span>
      <span class="type-badge <?= $acc['type'] ?>"><?= $typeLabel ?></span>
    </div>
    <div class="card-body">
      <div class="card-title"><?= htmlspecialchars($acc['title']) ?></div>
      <div class="card-meta">
        <?php if ($acc['rank']): ?><span>🏆 <?= htmlspecialchars($acc['rank']) ?></span><?php endif; ?>
        <?php if ($acc['level']): ?><span>⭐ Lv.<?= $acc['level'] ?></span><?php endif; ?>
        <?php if ($acc['skins_count']): ?><span>🎨 <?= $acc['skins_count'] ?> skin</span><?php endif; ?>
      </div>
      <div class="card-price">
        <?php if ($acc['type'] !== 'rent'): ?>
          <span class="price-buy">₮<?= number_format($acc['price']) ?></span>
        <?php endif; ?>
        <?php if ($acc['rental_price_per_day'] && $acc['type'] !== 'sale'): ?>
          <span class="price-rent">₮<?= number_format($acc['rental_price_per_day']) ?>/өдөр</span>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<footer>
  © 2024 <?= SITE_NAME ?> — Mobile Legends & PUBG Mobile Account Shop
</footer>

<script>
function filterSearch(q) {
  const cards = document.querySelectorAll('#accountGrid .card');
  cards.forEach(c => {
    c.style.display = c.dataset.title.includes(q.toLowerCase()) ? '' : 'none';
  });
}
</script>
</body>
</html>
