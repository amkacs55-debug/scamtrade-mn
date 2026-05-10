<?php
// chat.php — Хэрэглэгчийн чат (захиалгын дараа)
require_once 'config.php';
require_login();

$orderId = $_GET['order_id'] ?? '';
if (!$orderId) { header('Location: dashboard_user.php'); exit; }

// Order мэдээлэл авах
$orderRes = sb_get('orders', 'id=eq.' . urlencode($orderId) . '&select=*,accounts(*),users(username,email)');
$order = $orderRes['data'][0] ?? null;

// Зөвхөн өөрийн захиалгыг харах
if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
    header('Location: dashboard_user.php');
    exit;
}

$acc = $order['accounts'] ?? [];

// Bank мэдээлэл авах
$bankRes = sb_get('bank_accounts', 'is_active=eq.true&limit=1');
$bank = $bankRes['data'][0] ?? null;

// Мессеж илгээх
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['message'] ?? '');
    if ($content) {
        // Хэрэглэгчийн мессеж хадгалах
        sb_post('messages', [
            'order_id'    => $orderId,
            'sender_role' => 'user',
            'sender_id'   => $_SESSION['user_id'],
            'content'     => $content
        ], true);

        // AI автомат хариу
        $orderCtx = sprintf("Захиалга: %s, Тоглоом: %s, Үнэ: ₮%s, Төрөл: %s, Статус: %s",
            $orderId,
            $acc['game'] ?? '',
            number_format($order['total_price']),
            $order['order_type'],
            $order['status']
        );
        $aiReply = ai_auto_reply($content, $orderCtx);
        sb_post('messages', [
            'order_id'    => $orderId,
            'sender_role' => 'ai',
            'sender_id'   => null,
            'content'     => $aiReply
        ], true);

        header("Location: chat.php?order_id={$orderId}");
        exit;
    }
}

// Мессежүүд авах
$msgRes = sb_get('messages', 'order_id=eq.' . urlencode($orderId) . '&order=created_at.asc');
$messages = $msgRes['data'] ?? [];

$statusLabels = [
    'pending'   => ['⏳ Хүлээгдэж байна', '#f5a623'],
    'confirmed' => ['✅ Баталгаажсан', '#00e676'],
    'paid'      => ['💰 Төлөгдсөн', '#00d4ff'],
    'delivered' => ['🎮 Хүргэгдсэн', '#00e676'],
    'cancelled' => ['❌ Цуцлагдсан', '#ff5252'],
];
$statusInfo = $statusLabels[$order['status']] ?? ['❓ Тодорхойгүй', '#6b7a99'];
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Захиалга #<?= substr($orderId, 0, 8) ?> — Чат</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --bg:#050810;--bg2:#0a0f1e;--card:#0d1428;--border:#1a2545;--accent:#00d4ff;--accent2:#ff6b35;--text:#e8eaf6;--muted:#6b7a99;--success:#00e676; }
* { margin:0;padding:0;box-sizing:border-box; }
body { background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;height:100vh;display:flex;flex-direction:column; }
nav { background:rgba(10,15,30,0.95);border-bottom:1px solid var(--border);padding:0 1.5rem;height:60px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0; }
.logo { font-family:'Rajdhani',sans-serif;font-size:1.4rem;font-weight:700;background:linear-gradient(135deg,#00d4ff,#ff6b35);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none; }
.back { color:var(--muted);text-decoration:none;font-size:0.85rem; }
.back:hover { color:var(--accent); }

.main { flex:1;display:grid;grid-template-columns:300px 1fr;overflow:hidden; }
@media(max-width:700px) { .main { grid-template-columns:1fr; } .sidebar { display:none; } }

/* SIDEBAR */
.sidebar { border-right:1px solid var(--border);padding:1.2rem;overflow-y:auto;background:var(--bg2); }
.sidebar h4 { font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:1rem; }
.order-info { background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1rem;margin-bottom:1rem; }
.oi-row { display:flex;justify-content:space-between;font-size:0.82rem;padding:0.4rem 0;border-bottom:1px solid var(--border); }
.oi-row:last-child { border-bottom:none;font-weight:700; }
.oi-label { color:var(--muted); }

.status-badge { display:inline-block;padding:4px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;margin-bottom:0.5rem; }

.bank-card { background:var(--card);border:1px solid rgba(0,212,255,0.2);border-radius:12px;padding:1rem;margin-top:1rem; }
.bank-card h5 { font-family:'Rajdhani',sans-serif;font-size:0.95rem;font-weight:700;color:var(--accent);margin-bottom:0.8rem; }
.bank-row { font-size:0.82rem;padding:0.3rem 0;display:flex;justify-content:space-between; }
.bank-val { font-weight:700;color:var(--text); }
.copy-btn { background:rgba(0,212,255,0.1);border:1px solid var(--accent);color:var(--accent);padding:2px 8px;border-radius:6px;font-size:0.72rem;cursor:pointer;margin-left:0.3rem; }

/* CHAT AREA */
.chat-area { display:flex;flex-direction:column;overflow:hidden; }
.chat-header { padding:1rem 1.5rem;border-bottom:1px solid var(--border);background:var(--bg2); }
.chat-header h3 { font-family:'Rajdhani',sans-serif;font-size:1.1rem;font-weight:700; }
.chat-header p { font-size:0.8rem;color:var(--muted); }

.messages { flex:1;overflow-y:auto;padding:1.5rem;display:flex;flex-direction:column;gap:1rem; }
.msg { max-width:75%;animation:fadeIn 0.3s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
.msg.user { align-self:flex-end; }
.msg.admin, .msg.ai { align-self:flex-start; }
.msg-bubble { padding:0.7rem 1rem;border-radius:14px;font-size:0.88rem;line-height:1.5; }
.msg.user .msg-bubble { background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;border-bottom-right-radius:4px; }
.msg.admin .msg-bubble { background:var(--card);border:1px solid var(--border);border-bottom-left-radius:4px; }
.msg.ai .msg-bubble { background:rgba(0,212,255,0.08);border:1px solid rgba(0,212,255,0.2);border-bottom-left-radius:4px;color:var(--accent); }
.msg-meta { font-size:0.7rem;color:var(--muted);margin-top:3px; }
.msg.user .msg-meta { text-align:right; }
.sender-tag { font-size:0.7rem;font-weight:700;margin-bottom:3px;color:var(--muted); }

.chat-input-area { padding:1rem 1.5rem;border-top:1px solid var(--border);background:var(--bg2);display:flex;gap:0.8rem;align-items:flex-end; }
.chat-input-area textarea { flex:1;background:var(--card);border:1px solid var(--border);color:var(--text);padding:0.7rem 1rem;border-radius:12px;font-size:0.88rem;outline:none;resize:none;font-family:'Inter',sans-serif;max-height:120px;transition:border-color 0.2s; }
.chat-input-area textarea:focus { border-color:var(--accent); }
.send-btn { background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;border:none;width:44px;height:44px;border-radius:12px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:opacity 0.2s; }
.send-btn:hover { opacity:0.85; }
</style>
</head>
<body>
<nav>
  <a href="index.php" class="logo">⚔ ML&PUBG Shop</a>
  <a href="dashboard_user.php" class="back">← Миний захиалга</a>
</nav>

<div class="main">
  <!-- SIDEBAR: Order info -->
  <div class="sidebar">
    <h4>📋 Захиалга</h4>
    <span class="status-badge" style="background:<?= $statusInfo[1] ?>22;color:<?= $statusInfo[1] ?>;border:1px solid <?= $statusInfo[1] ?>"><?= $statusInfo[0] ?></span>
    <div class="order-info">
      <div class="oi-row"><span class="oi-label">Тоглоом</span><span><?= $acc['game'] === 'mlbb' ? '⚔ ML' : '🔫 PUBG' ?></span></div>
      <div class="oi-row"><span class="oi-label">Account</span><span style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars(substr($acc['title'] ?? '', 0, 25)) ?></span></div>
      <div class="oi-row"><span class="oi-label">Төрөл</span><span><?= $order['order_type'] === 'buy' ? '🛒 Худалдан авах' : '📅 Түрээс' ?></span></div>
      <?php if ($order['order_type'] === 'rent'): ?>
      <div class="oi-row"><span class="oi-label">Хугацаа</span><span><?= $order['rental_days'] ?> өдөр</span></div>
      <?php endif; ?>
      <div class="oi-row"><span class="oi-label">Нийт дүн</span><span style="color:var(--success)">₮<?= number_format($order['total_price']) ?></span></div>
    </div>

    <?php if ($bank && in_array($order['status'], ['pending','confirmed'])): ?>
    <div class="bank-card">
      <h5>💳 Төлбөрийн данс</h5>
      <div class="bank-row"><span class="oi-label">Банк</span><span class="bank-val"><?= htmlspecialchars($bank['bank_name']) ?></span></div>
      <div class="bank-row">
        <span class="oi-label">Дансны дугаар</span>
        <span>
          <span class="bank-val" id="accNum"><?= htmlspecialchars($bank['account_number']) ?></span>
          <button class="copy-btn" onclick="copyText('accNum')">Хуулах</button>
        </span>
      </div>
      <div class="bank-row"><span class="oi-label">Эзэмшигч</span><span class="bank-val"><?= htmlspecialchars($bank['account_name']) ?></span></div>
      <div class="bank-row"><span class="oi-label">Дүн</span><span class="bank-val" style="color:var(--success)">₮<?= number_format($order['total_price']) ?></span></div>
      <p style="font-size:0.72rem;color:var(--muted);margin-top:0.6rem">Гүйлгээ хийсний дараа чатаар мэдэгдэнэ үү.</p>
    </div>
    <?php endif; ?>
  </div>

  <!-- CHAT AREA -->
  <div class="chat-area">
    <div class="chat-header">
      <h3>💬 Захиалгын чат</h3>
      <p>Захиалга #<?= substr($orderId, 0, 8) ?>... | Асуулт байвал доор бичнэ үү</p>
    </div>

    <div class="messages" id="msgArea">
      <?php if (empty($messages)): ?>
        <div style="text-align:center;color:var(--muted);padding:2rem">Мессеж байхгүй байна.</div>
      <?php endif; ?>
      <?php foreach ($messages as $m): ?>
        <?php $role = $m['sender_role']; ?>
        <div class="msg <?= $role ?>">
          <?php if ($role !== 'user'): ?>
            <div class="sender-tag"><?= $role === 'ai' ? '🤖 Автомат туслах' : '👤 Админ' ?></div>
          <?php endif; ?>
          <div class="msg-bubble"><?= nl2br(htmlspecialchars($m['content'])) ?></div>
          <div class="msg-meta"><?= date('H:i', strtotime($m['created_at'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <form class="chat-input-area" method="POST">
      <textarea name="message" placeholder="Мессеж бичнэ үү..." rows="1"
        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();this.form.submit()}"
        oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
      <button type="submit" class="send-btn">↑</button>
    </form>
  </div>
</div>

<script>
// Scroll to bottom
const msgArea = document.getElementById('msgArea');
msgArea.scrollTop = msgArea.scrollHeight;

function copyText(id) {
  const text = document.getElementById(id).textContent;
  navigator.clipboard.writeText(text).then(() => {
    alert('Хуулагдлаа: ' + text);
  });
}
</script>
</body>
</html>
