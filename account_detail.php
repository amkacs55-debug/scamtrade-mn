<?php
// account_detail.php
require_once 'config.php';

$id = $_GET['id'] ?? '';
if (!$id) { header('Location: index.php'); exit; }

$res = sb_get('accounts', 'id=eq.' . urlencode($id));
$acc = $res['data'][0] ?? null;
if (!$acc) { header('Location: index.php'); exit; }

$gameLabel = $acc['game'] === 'mlbb' ? 'Mobile Legends' : 'PUBG Mobile';
$emoji = $acc['game'] === 'mlbb' ? '⚔️' : '🔫';

$error = '';
$success = '';

// Order submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $orderType  = $_POST['order_type'] ?? '';
    $rentalDays = (int)($_POST['rental_days'] ?? 1);

    if ($orderType === 'buy' && in_array($acc['type'], ['sale','both'])) {
        $total = $acc['price'];
        $rentalStart = null;
        $rentalEnd = null;
    } elseif ($orderType === 'rent' && in_array($acc['type'], ['rent','both'])) {
        $total = $acc['rental_price_per_day'] * $rentalDays;
        $rentalStart = date('Y-m-d');
        $rentalEnd = date('Y-m-d', strtotime("+{$rentalDays} days"));
    } else {
        $error = 'Буруу захиалгын төрөл.';
    }

    if (!$error) {
        $orderRes = sb_post('orders', [
            'user_id'      => $_SESSION['user_id'],
            'account_id'   => $acc['id'],
            'order_type'   => $orderType,
            'rental_days'  => $orderType === 'rent' ? $rentalDays : null,
            'rental_start' => $rentalStart,
            'rental_end'   => $rentalEnd,
            'total_price'  => $total,
            'status'       => 'pending'
        ], true);

        if ($orderRes['status'] === 201) {
            $orderId = $orderRes['data'][0]['id'];
            // Auto AI message
            $orderCtx = "Захиалгын дугаар: {$orderId}, Үнэ: ₮" . number_format($total) . ", Төрөл: {$orderType}";
            $aiMsg = ai_auto_reply('Захиалга хийлээ', $orderCtx);
            sb_post('messages', [
                'order_id'    => $orderId,
                'sender_role' => 'ai',
                'sender_id'   => null,
                'content'     => $aiMsg
            ], true);

            header("Location: chat.php?order_id={$orderId}");
            exit;
        } else {
            $error = 'Захиалга үүсгэхэд алдаа гарлаа.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($acc['title']) ?> — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --bg:#050810;--bg2:#0a0f1e;--card:#0d1428;--border:#1a2545;--accent:#00d4ff;--accent2:#ff6b35;--text:#e8eaf6;--muted:#6b7a99;--success:#00e676;--danger:#ff5252; }
* { margin:0;padding:0;box-sizing:border-box; }
body { background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh; }
nav { background:rgba(10,15,30,0.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100; }
.logo { font-family:'Rajdhani',sans-serif;font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#00d4ff,#ff6b35);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none; }
.back-btn { color:var(--muted);text-decoration:none;font-size:0.88rem; }
.back-btn:hover { color:var(--accent); }
.container { max-width:1100px;margin:2rem auto;padding:0 1.5rem;display:grid;grid-template-columns:1fr 380px;gap:2rem; }
@media(max-width:768px) { .container { grid-template-columns:1fr; } }

.images-gallery { background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden; }
.main-img { width:100%;height:300px;object-fit:cover;background:linear-gradient(135deg,#0d1428,#1a2545);display:flex;align-items:center;justify-content:center;font-size:5rem; }
.main-img img { width:100%;height:100%;object-fit:cover; }
.thumb-row { display:flex;gap:0.5rem;padding:0.7rem; }
.thumb { width:60px;height:60px;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:border-color 0.2s;background:var(--bg2); }
.thumb:hover { border-color:var(--accent); }
.thumb img { width:100%;height:100%;object-fit:cover; }

.info-card { background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem; }
.game-tag { display:inline-block;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;margin-bottom:0.8rem; }
.ml-tag { background:rgba(30,144,255,0.15);color:#5b9ff5;border:1px solid rgba(30,144,255,0.3); }
.pubg-tag { background:rgba(245,166,35,0.15);color:#f5a623;border:1px solid rgba(245,166,35,0.3); }
h1 { font-family:'Rajdhani',sans-serif;font-size:1.8rem;font-weight:700;margin-bottom:1rem; }
.stats-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:0.8rem;margin-bottom:1.2rem; }
.stat-box { background:var(--bg2);border-radius:10px;padding:0.8rem;text-align:center; }
.stat-val { font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:var(--accent); }
.stat-lbl { font-size:0.72rem;color:var(--muted);margin-top:2px; }
.desc { color:var(--muted);font-size:0.9rem;line-height:1.7; }

/* ORDER CARD */
.order-card { background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;position:sticky;top:80px; }
.order-card h3 { font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;margin-bottom:1.2rem; }
.tab-group { display:flex;gap:0.5rem;margin-bottom:1.2rem; }
.tab { flex:1;padding:0.6rem;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--muted);font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.2s;font-family:'Inter',sans-serif; }
.tab.active { background:var(--accent);color:#000;border-color:var(--accent); }
.tab.rent-tab.active { background:var(--accent2);color:#fff;border-color:var(--accent2); }
.tab:disabled { opacity:0.3;cursor:not-allowed; }
.price-display { font-family:'Rajdhani',sans-serif;font-size:2rem;font-weight:700;color:var(--success);margin:1rem 0; }
.rent-control { display:none;margin-bottom:1rem; }
.rent-control label { font-size:0.82rem;color:var(--muted);display:block;margin-bottom:0.4rem; }
.days-input { display:flex;align-items:center;gap:0.5rem; }
.days-input input { flex:1;background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:0.6rem;border-radius:8px;font-size:1rem;text-align:center;outline:none;font-family:'Rajdhani',sans-serif;font-weight:700; }
.days-btn { background:var(--bg2);border:1px solid var(--border);color:var(--text);width:36px;height:36px;border-radius:8px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;transition:all 0.2s; }
.days-btn:hover { border-color:var(--accent);color:var(--accent); }
.order-btn { width:100%;padding:0.9rem;background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;font-weight:700;font-size:1rem;border:none;border-radius:10px;cursor:pointer;font-family:'Rajdhani',sans-serif;letter-spacing:0.5px;margin-top:0.5rem;transition:opacity 0.2s; }
.order-btn:hover { opacity:0.85; }
.login-prompt { text-align:center;padding:1rem;background:rgba(0,212,255,0.05);border:1px solid rgba(0,212,255,0.2);border-radius:10px;margin-top:0.5rem; }
.login-prompt a { color:var(--accent);text-decoration:none;font-weight:600; }
.error-msg { background:rgba(255,82,82,0.1);border:1px solid var(--danger);color:var(--danger);padding:0.7rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem; }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">⚔ ML&PUBG Shop</a>
  <a href="javascript:history.back()" class="back-btn">← Буцах</a>
</nav>

<div class="container">
  <!-- LEFT: Images + Info -->
  <div>
    <div class="images-gallery">
      <div class="main-img" id="mainImg">
        <?php if (!empty($acc['images']) && $acc['images'][0]): ?>
          <img src="<?= htmlspecialchars($acc['images'][0]) ?>" id="mainImgEl">
        <?php else: ?>
          <span><?= $emoji ?></span>
        <?php endif; ?>
      </div>
      <?php if (!empty($acc['images']) && count($acc['images']) > 1): ?>
      <div class="thumb-row">
        <?php foreach ($acc['images'] as $i => $img): ?>
          <div class="thumb" onclick="document.getElementById('mainImgEl').src='<?= htmlspecialchars($img) ?>'">
            <img src="<?= htmlspecialchars($img) ?>">
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="info-card" style="margin-top:1.5rem">
      <span class="game-tag <?= $acc['game'] === 'mlbb' ? 'ml-tag' : 'pubg-tag' ?>"><?= $emoji ?> <?= $gameLabel ?></span>
      <h1><?= htmlspecialchars($acc['title']) ?></h1>
      <div class="stats-grid">
        <?php if ($acc['rank']): ?>
        <div class="stat-box"><div class="stat-val">🏆</div><div class="stat-lbl"><?= htmlspecialchars($acc['rank']) ?></div></div>
        <?php endif; ?>
        <?php if ($acc['level']): ?>
        <div class="stat-box"><div class="stat-val"><?= $acc['level'] ?></div><div class="stat-lbl">Level</div></div>
        <?php endif; ?>
        <?php if ($acc['skins_count']): ?>
        <div class="stat-box"><div class="stat-val"><?= $acc['skins_count'] ?></div><div class="stat-lbl">Skin</div></div>
        <?php endif; ?>
      </div>
      <?php if ($acc['description']): ?>
        <p class="desc"><?= nl2br(htmlspecialchars($acc['description'])) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT: Order -->
  <div>
    <div class="order-card">
      <h3>💳 Захиалах</h3>
      <?php if ($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <?php if (!is_logged_in()): ?>
        <div class="login-prompt">
          Захиалахын тулд <a href="login.php">нэвтэрнэ үү</a> эсвэл <a href="register.php">бүртгүүлнэ үү</a>.
        </div>
      <?php else: ?>
      <form method="POST" id="orderForm">
        <input type="hidden" name="order_type" id="orderTypeInput" value="buy">

        <div class="tab-group">
          <?php if (in_array($acc['type'], ['sale','both'])): ?>
          <button type="button" class="tab active" id="buyTab" onclick="setTab('buy')">🛒 Худалдаж авах</button>
          <?php endif; ?>
          <?php if (in_array($acc['type'], ['rent','both'])): ?>
          <button type="button" class="tab rent-tab" id="rentTab" onclick="setTab('rent')">📅 Түрээслэх</button>
          <?php endif; ?>
        </div>

        <div class="price-display" id="priceDisplay">
          ₮<?= number_format($acc['price'] ?? 0) ?>
        </div>

        <div class="rent-control" id="rentControl">
          <label>Түрээслэх хугацаа (өдөр)</label>
          <div class="days-input">
            <button type="button" class="days-btn" onclick="changeDays(-1)">−</button>
            <input type="number" name="rental_days" id="daysInput" value="1" min="1" max="30" onchange="updateRentPrice()">
            <button type="button" class="days-btn" onclick="changeDays(1)">+</button>
          </div>
          <p style="font-size:0.78rem;color:var(--muted);margin-top:0.5rem" id="rentDates"></p>
        </div>

        <button type="submit" class="order-btn">⚡ ЗАХИАЛГА ҮҮСГЭХ</button>
        <p style="font-size:0.75rem;color:var(--muted);margin-top:0.8rem;text-align:center">Захиалсны дараа чатаар холбогдоно</p>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const buyPrice  = <?= floatval($acc['price'] ?? 0) ?>;
const rentPrice = <?= floatval($acc['rental_price_per_day'] ?? 0) ?>;
let currentTab = 'buy';

function setTab(tab) {
  currentTab = tab;
  document.getElementById('orderTypeInput').value = tab;
  document.getElementById('buyTab')?.classList.toggle('active', tab==='buy');
  document.getElementById('rentTab')?.classList.toggle('active', tab==='rent');
  document.getElementById('rentControl').style.display = tab==='rent' ? 'block' : 'none';
  updatePrice();
}

function updatePrice() {
  if (currentTab === 'buy') {
    document.getElementById('priceDisplay').textContent = '₮' + buyPrice.toLocaleString();
  } else {
    updateRentPrice();
  }
}

function updateRentPrice() {
  const days = parseInt(document.getElementById('daysInput').value) || 1;
  const total = rentPrice * days;
  document.getElementById('priceDisplay').textContent = '₮' + total.toLocaleString() + ' (' + days + ' өдөр)';
  const start = new Date();
  const end = new Date();
  end.setDate(end.getDate() + days);
  document.getElementById('rentDates').textContent =
    start.toLocaleDateString('mn-MN') + ' → ' + end.toLocaleDateString('mn-MN');
}

function changeDays(d) {
  const inp = document.getElementById('daysInput');
  inp.value = Math.max(1, Math.min(30, parseInt(inp.value) + d));
  updateRentPrice();
}

// Init
<?php if (!in_array($acc['type'], ['sale','both'])): ?>
setTab('rent');
<?php else: ?>
updatePrice();
<?php endif; ?>
</script>
</body>
</html>
