<?php
// dashboard_user.php
require_once 'config.php';
require_login();

$ordersRes = sb_get('orders', 'user_id=eq.' . $_SESSION['user_id'] . '&select=*,accounts(title,game,type)&order=created_at.desc');
$orders = $ordersRes['data'] ?? [];

$statusLabels = [
    'pending'   => ['⏳ Хүлээгдэж байна', '#f5a623'],
    'confirmed' => ['✅ Баталгаажсан', '#00e676'],
    'paid'      => ['💰 Төлөгдсөн', '#00d4ff'],
    'delivered' => ['🎮 Хүргэгдсэн', '#00e676'],
    'cancelled' => ['❌ Цуцлагдсан', '#ff5252'],
];
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Миний захиалга — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --bg:#050810;--bg2:#0a0f1e;--card:#0d1428;--border:#1a2545;--accent:#00d4ff;--accent2:#ff6b35;--text:#e8eaf6;--muted:#6b7a99;--success:#00e676;--danger:#ff5252; }
* { margin:0;padding:0;box-sizing:border-box; }
body { background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh; }
nav { background:rgba(10,15,30,0.95);border-bottom:1px solid var(--border);padding:0 2rem;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100; }
.logo { font-family:'Rajdhani',sans-serif;font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#00d4ff,#ff6b35);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none; }
.nav-r { display:flex;gap:1rem;align-items:center; }
.nav-r a { color:var(--muted);text-decoration:none;font-size:0.88rem; }
.nav-r a:hover { color:var(--accent); }
.container { max-width:900px;margin:2rem auto;padding:0 1.5rem; }
h2 { font-family:'Rajdhani',sans-serif;font-size:1.8rem;font-weight:700;margin-bottom:1.5rem; }
.order-card { background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.2rem 1.5rem;margin-bottom:1rem;display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:center;transition:border-color 0.2s; }
.order-card:hover { border-color:var(--accent); }
.oc-game { font-size:0.78rem;color:var(--muted);margin-bottom:0.3rem; }
.oc-title { font-family:'Rajdhani',sans-serif;font-size:1.1rem;font-weight:700;margin-bottom:0.4rem; }
.oc-meta { display:flex;gap:1rem;font-size:0.78rem;color:var(--muted); }
.status-badge { display:inline-block;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;margin-bottom:0.5rem;white-space:nowrap; }
.oc-price { font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:var(--success); }
.chat-btn { display:inline-block;margin-top:0.5rem;padding:5px 14px;background:rgba(0,212,255,0.1);border:1px solid var(--accent);color:var(--accent);border-radius:8px;font-size:0.8rem;text-decoration:none;transition:all 0.2s; }
.chat-btn:hover { background:var(--accent);color:#000; }
.empty { text-align:center;padding:4rem 2rem;color:var(--muted); }
.empty-icon { font-size:4rem;margin-bottom:1rem; }
.btn { padding:0.5rem 1.2rem;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;border:none;text-decoration:none;display:inline-block;font-family:'Inter',sans-serif; }
.btn-primary { background:linear-gradient(135deg,#00d4ff,#0099cc);color:#000; }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">⚔ ML&PUBG Shop</a>
  <div class="nav-r">
    <a href="index.php">🎮 Дэлгүүр</a>
    <a href="logout.php">Гарах</a>
    <span style="color:var(--accent);font-size:0.85rem">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
  </div>
</nav>

<div class="container">
  <h2>📦 Миний захиалга</h2>

  <?php if (empty($orders)): ?>
    <div class="empty">
      <div class="empty-icon">📭</div>
      <p>Одоогоор захиалга байхгүй байна.</p>
      <br>
      <a href="index.php" class="btn btn-primary">🎮 Account харах</a>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $o): ?>
    <?php
      $si = $statusLabels[$o['status']] ?? ['❓', '#6b7a99'];
      $acc = $o['accounts'] ?? [];
      $gameLabel = ($acc['game'] ?? '') === 'mlbb' ? '⚔ Mobile Legends' : '🔫 PUBG Mobile';
    ?>
    <div class="order-card">
      <div>
        <div class="oc-game"><?= $gameLabel ?></div>
        <div class="oc-title"><?= htmlspecialchars($acc['title'] ?? 'Account') ?></div>
        <div class="oc-meta">
          <span><?= $o['order_type'] === 'buy' ? '🛒 Худалдан авах' : '📅 Түрээс ' . $o['rental_days'] . ' өдөр' ?></span>
          <span><?= date('Y.m.d', strtotime($o['created_at'])) ?></span>
        </div>
        <a href="chat.php?order_id=<?= $o['id'] ?>" class="chat-btn">💬 Чат / Дэлгэрэнгүй</a>
      </div>
      <div style="text-align:right">
        <span class="status-badge" style="background:<?= $si[1] ?>22;color:<?= $si[1] ?>;border:1px solid <?= $si[1] ?>"><?= $si[0] ?></span>
        <div class="oc-price">₮<?= number_format($o['total_price']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
