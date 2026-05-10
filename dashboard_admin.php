<?php
// dashboard_admin.php
require_once 'config.php';
require_admin();

$tab = $_GET['tab'] ?? 'orders';

// Account зар оруулах
$postSuccess = '';
$postError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_account'])) {
    $tab = 'post';

    // ---- Зурагнуудыг upload хийх ----
    $uploadedUrls = [];

    // 1) Файл upload (олон зураг)
    if (!empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $count = count($files['name']);
        for ($i = 0; $i < min($count, 5); $i++) {
            $single = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            if ($single['error'] === UPLOAD_ERR_OK) {
                $url = upload_image_to_supabase($single);
                if ($url) $uploadedUrls[] = $url;
                else $postError .= "Зураг {$single['name']} upload амжилтгүй. ";
            }
        }
    }

    // 2) Гараар оруулсан URL-ууд (нэмэлт)
    if (!empty($_POST['image_url'])) {
        foreach (explode("\n", $_POST['image_url']) as $line) {
            $line = trim($line);
            if (filter_var($line, FILTER_VALIDATE_URL)) {
                $uploadedUrls[] = $line;
            }
        }
    }

    $data = [
        'game'                 => $_POST['game'],
        'title'                => trim($_POST['title']),
        'description'          => trim($_POST['description']),
        'rank'                 => trim($_POST['rank']),
        'skins_count'          => (int)$_POST['skins_count'],
        'level'                => (int)$_POST['level'],
        'price'                => (float)$_POST['price'],
        'rental_price_per_day' => $_POST['rental_price'] ? (float)$_POST['rental_price'] : null,
        'type'                 => $_POST['type'],
        'status'               => 'available',
        'posted_by'            => $_SESSION['user_id'],
        'images'               => $uploadedUrls,
    ];

    $res = sb_post('accounts', $data, true);
    if ($res['status'] === 201) {
        $postSuccess = 'Account амжилттай нийтлэгдлээ! ' . count($uploadedUrls) . ' зураг оруулсан.';
    } else {
        $postError .= 'Алдаа гарлаа: ' . json_encode($res['data']);
    }
}

// Банкны данс нэмэх
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bank'])) {
    sb_post('bank_accounts', [
        'bank_name'      => trim($_POST['bank_name']),
        'account_number' => trim($_POST['account_number']),
        'account_name'   => trim($_POST['account_name']),
    ], true);
    $tab = 'bank';
}

// Захиалгын статус өөрчлөх
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    sb_patch('orders', 'id=eq.' . $_POST['order_id'], ['status' => $_POST['new_status']], true);
    header('Location: dashboard_admin.php?tab=orders');
    exit;
}

// Data авах
$ordersRes  = sb_get('orders', 'select=*,accounts(title,game),users(username,email)&order=created_at.desc');
$orders     = $ordersRes['data'] ?? [];
$accountsRes= sb_get('accounts', 'order=created_at.desc');
$accounts   = $accountsRes['data'] ?? [];
$bankRes    = sb_get('bank_accounts', 'is_active=eq.true');
$banks      = $bankRes['data'] ?? [];

// Чат дарж нээх
$chatOrderId = $_GET['chat'] ?? null;
$chatMessages = [];
$chatOrder = null;
if ($chatOrderId) {
    $cr = sb_get('orders', 'id=eq.' . urlencode($chatOrderId) . '&select=*,accounts(title,game),users(username)');
    $chatOrder = $cr['data'][0] ?? null;
    $mr = sb_get('messages', 'order_id=eq.' . urlencode($chatOrderId) . '&order=created_at.asc');
    $chatMessages = $mr['data'] ?? [];

    // Админ хариу илгээх
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_msg'])) {
        $content = trim($_POST['admin_msg']);
        if ($content) {
            sb_post('messages', [
                'order_id'    => $chatOrderId,
                'sender_role' => 'admin',
                'sender_id'   => $_SESSION['user_id'],
                'content'     => $content,
            ], true);
            header('Location: dashboard_admin.php?tab=orders&chat=' . $chatOrderId);
            exit;
        }
    }
}

$statusLabels = ['pending'=>'⏳ Хүлээж байна','confirmed'=>'✅ Баталгаажсан','paid'=>'💰 Төлөгдсөн','delivered'=>'🎮 Хүргэгдсэн','cancelled'=>'❌ Цуцлагдсан'];
$statusColors = ['pending'=>'#f5a623','confirmed'=>'#00e676','paid'=>'#00d4ff','delivered'=>'#00e676','cancelled'=>'#ff5252'];
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --bg:#030608;--bg2:#080d1a;--card:#0b1020;--border:#151f38;--accent:#00d4ff;--accent2:#ff6b35;--text:#e8eaf6;--muted:#6b7a99;--success:#00e676;--danger:#ff5252; }
* { margin:0;padding:0;box-sizing:border-box; }
body { background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;display:flex; }

/* SIDEBAR NAV */
.side-nav { width:220px;background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:1.5rem 1rem;flex-shrink:0;min-height:100vh; }
.logo { font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;background:linear-gradient(135deg,#00d4ff,#ff6b35);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none;margin-bottom:2rem;display:block; }
.admin-label { font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:0.8rem;padding-left:0.5rem; }
.nav-item { display:block;padding:0.6rem 0.8rem;border-radius:10px;color:var(--muted);text-decoration:none;font-size:0.88rem;transition:all 0.2s;margin-bottom:0.2rem; }
.nav-item:hover, .nav-item.active { background:rgba(0,212,255,0.1);color:var(--accent); }
.nav-item.active { font-weight:600; }
.nav-bottom { margin-top:auto; }
.nav-bottom a { display:block;color:var(--muted);text-decoration:none;font-size:0.82rem;padding:0.4rem 0.8rem; }
.nav-bottom a:hover { color:var(--danger); }

/* MAIN */
.main { flex:1;overflow:auto;padding:2rem; }
h2 { font-family:'Rajdhani',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:1.5rem; }

/* CARDS */
.order-table { width:100%;border-collapse:collapse; }
.order-table th { text-align:left;font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:0.6rem 1rem;border-bottom:1px solid var(--border); }
.order-table td { padding:0.8rem 1rem;border-bottom:1px solid var(--border);font-size:0.85rem;vertical-align:middle; }
.order-table tr:hover td { background:rgba(0,212,255,0.03); }
.s-badge { display:inline-block;padding:3px 10px;border-radius:12px;font-size:0.72rem;font-weight:700; }
.btn-sm { padding:4px 12px;border-radius:6px;font-size:0.78rem;font-weight:600;cursor:pointer;border:none;font-family:'Inter',sans-serif; }
.btn-chat { background:rgba(0,212,255,0.1);border:1px solid var(--accent);color:var(--accent); }
.btn-chat:hover { background:var(--accent);color:#000; }
select.status-sel { background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:3px 8px;border-radius:6px;font-size:0.78rem;font-family:'Inter',sans-serif; }

/* FORM */
.form-card { background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.5rem;max-width:600px; }
.form-group { margin-bottom:1rem; }
.form-group label { display:block;font-size:0.8rem;color:var(--muted);margin-bottom:0.4rem;font-weight:500; }
.form-group input, .form-group textarea, .form-group select {
  width:100%;background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:9px;font-size:0.88rem;outline:none;font-family:'Inter',sans-serif;transition:border-color 0.2s;
}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color:var(--accent); }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:1rem; }
.btn-primary { padding:0.7rem 1.5rem;background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;font-weight:700;font-size:0.9rem;border:none;border-radius:10px;cursor:pointer;font-family:'Rajdhani',sans-serif;letter-spacing:0.5px; }
.btn-primary:hover { opacity:0.85; }
.success-msg { background:rgba(0,230,118,0.1);border:1px solid var(--success);color:var(--success);padding:0.7rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem; }
.error-msg { background:rgba(255,82,82,0.1);border:1px solid var(--danger);color:var(--danger);padding:0.7rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem; }

/* CHAT PANEL */
.chat-panel { position:fixed;right:0;top:0;bottom:0;width:380px;background:var(--bg2);border-left:1px solid var(--border);display:flex;flex-direction:column;z-index:200;transform:translateX(<?= $chatOrderId ? '0' : '100%' ?>);transition:transform 0.3s; }
.chat-panel-header { padding:1rem 1.2rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between; }
.chat-panel-header h4 { font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700; }
.close-chat { color:var(--muted);text-decoration:none;font-size:1.2rem; }
.chat-msgs { flex:1;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:0.8rem; }
.cm { max-width:85%; }
.cm.user { align-self:flex-end; }
.cm.admin, .cm.ai { align-self:flex-start; }
.cm-bubble { padding:0.6rem 0.9rem;border-radius:12px;font-size:0.83rem;line-height:1.5; }
.cm.user .cm-bubble { background:rgba(0,212,255,0.15);border:1px solid rgba(0,212,255,0.3); }
.cm.admin .cm-bubble { background:linear-gradient(135deg,var(--accent),#0099cc);color:#000; }
.cm.ai .cm-bubble { background:rgba(255,107,53,0.1);border:1px solid rgba(255,107,53,0.3);color:var(--accent2); }
.cm-meta { font-size:0.68rem;color:var(--muted);margin-top:2px; }
.chat-send { padding:0.8rem;border-top:1px solid var(--border);display:flex;gap:0.5rem; }
.chat-send input { flex:1;background:var(--card);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:9px;font-size:0.85rem;outline:none;font-family:'Inter',sans-serif; }
.chat-send input:focus { border-color:var(--accent); }
.chat-send button { background:var(--accent);color:#000;border:none;padding:0.6rem 1rem;border-radius:9px;font-weight:700;cursor:pointer;font-size:0.85rem; }

/* ACCOUNTS LIST */
.acc-list { display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem;margin-top:0; }
.acc-mini { background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1rem;display:flex;flex-direction:column;gap:0.4rem; }
.acc-mini h4 { font-family:'Rajdhani',sans-serif;font-size:1rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.acc-mini .mini-meta { font-size:0.75rem;color:var(--muted); }
.acc-mini .mini-price { color:var(--success);font-family:'Rajdhani',sans-serif;font-size:1.1rem;font-weight:700; }
</style>
</head>
<body>

<div class="side-nav">
  <a href="index.php" class="logo">⚔ ML&PUBG</a>
  <div class="admin-label">Admin Panel</div>
  <a href="?tab=orders" class="nav-item <?= $tab==='orders'?'active':'' ?>">📦 Захиалга</a>
  <a href="?tab=accounts" class="nav-item <?= $tab==='accounts'?'active':'' ?>">🎮 Account-ууд</a>
  <a href="?tab=post" class="nav-item <?= $tab==='post'?'active':'' ?>">➕ Account нэмэх</a>
  <a href="?tab=bank" class="nav-item <?= $tab==='bank'?'active':'' ?>">💳 Банкны данс</a>
  <div class="nav-bottom">
    <a href="index.php">🏠 Нүүр</a>
    <a href="logout.php" style="color:var(--danger)">🚪 Гарах</a>
  </div>
</div>

<div class="main">

<?php if ($tab === 'orders'): ?>
  <h2>📦 Бүх захиалга</h2>
  <div style="overflow-x:auto">
  <table class="order-table">
    <thead>
      <tr>
        <th>Хэрэглэгч</th><th>Account</th><th>Төрөл</th><th>Дүн</th><th>Статус</th><th>Огноо</th><th>Үйлдэл</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o):
      $sc = $statusColors[$o['status']] ?? '#6b7a99';
      $sl = $statusLabels[$o['status']] ?? '❓';
    ?>
      <tr>
        <td><?= htmlspecialchars($o['users']['username'] ?? '-') ?></td>
        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($o['accounts']['title'] ?? '-') ?></td>
        <td><?= $o['order_type'] === 'buy' ? '🛒 Авах' : '📅 Түрээс' ?></td>
        <td style="color:var(--success);font-family:'Rajdhani',sans-serif;font-weight:700">₮<?= number_format($o['total_price']) ?></td>
        <td>
          <form method="POST" style="display:inline">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <input type="hidden" name="update_status" value="1">
            <select name="new_status" class="status-sel" onchange="this.form.submit()">
              <?php foreach ($statusLabels as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $o['status']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td style="color:var(--muted);font-size:0.78rem"><?= date('m/d H:i', strtotime($o['created_at'])) ?></td>
        <td>
          <a href="?tab=orders&chat=<?= $o['id'] ?>" class="btn-sm btn-chat">💬 Чат</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>

<?php elseif ($tab === 'accounts'): ?>
  <h2>🎮 Account-ууд <span style="font-size:1rem;color:var(--muted)">(<?= count($accounts) ?>)</span></h2>
  <div class="acc-list">
  <?php foreach ($accounts as $a): ?>
    <div class="acc-mini">
      <div class="mini-meta"><?= $a['game']==='mlbb' ? '⚔ ML' : '🔫 PUBG' ?> · <?= ucfirst($a['type']) ?> · <?= $a['status'] ?></div>
      <h4><?= htmlspecialchars($a['title']) ?></h4>
      <div class="mini-meta">🏆 <?= htmlspecialchars($a['rank'] ?? '-') ?> · Lv.<?= $a['level'] ?> · <?= $a['skins_count'] ?> skin</div>
      <div class="mini-price">₮<?= number_format($a['price']) ?></div>
    </div>
  <?php endforeach; ?>
  </div>

<?php elseif ($tab === 'post'): ?>
  <h2>➕ Шинэ Account нийтлэх</h2>
  <?php if ($postSuccess): ?><div class="success-msg">✅ <?= htmlspecialchars($postSuccess) ?></div><?php endif; ?>
  <?php if ($postError): ?><div class="error-msg">⚠️ <?= htmlspecialchars($postError) ?></div><?php endif; ?>
  <div class="form-card">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="post_account" value="1">
      <div class="form-row">
        <div class="form-group">
          <label>Тоглоом</label>
          <select name="game" required>
            <option value="mlbb">⚔ Mobile Legends</option>
            <option value="pubg">🔫 PUBG Mobile</option>
          </select>
        </div>
        <div class="form-group">
          <label>Төрөл</label>
          <select name="type" required>
            <option value="sale">Зарах</option>
            <option value="rent">Түрээслэх</option>
            <option value="both">Зарах & Түрээс</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Гарчиг</label>
        <input type="text" name="title" placeholder="жнь: Mythical Glory | 150+ skin | Max rank" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Rank</label>
          <input type="text" name="rank" placeholder="Mythical Glory">
        </div>
        <div class="form-group">
          <label>Level</label>
          <input type="number" name="level" value="0" min="0">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Skin тоо</label>
          <input type="number" name="skins_count" value="0" min="0">
        </div>
        <div class="form-group">
          <label>Зарах үнэ (₮)</label>
          <input type="number" name="price" step="1000" placeholder="150000" required>
        </div>
      </div>
      <div class="form-group">
        <label>Түрээсийн үнэ өдөрт (₮, хэрэггүй бол хоосон)</label>
        <input type="number" name="rental_price" step="500" placeholder="5000">
      </div>

      <!-- ====== ЗУРАГ UPLOAD ХЭСЭГ ====== -->
      <div class="form-group">
        <label>🖼 Зурагнууд (хамгийн ихдээ 5, JPG/PNG/WEBP, 5MB хүртэл)</label>

        <!-- Drag & Drop zone -->
        <div class="upload-zone" id="uploadZone" onclick="document.getElementById('imgInput').click()">
          <div class="upload-icon">📁</div>
          <div class="upload-text">Зургаа эндрүү чирж тавь эсвэл <span class="upload-link">сонго</span></div>
          <div class="upload-hint">JPG, PNG, WEBP · Нэг дор 5 зураг хүртэл</div>
          <input type="file" id="imgInput" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="handleFiles(this.files)">
        </div>

        <!-- Preview grid -->
        <div class="img-preview-grid" id="previewGrid"></div>

        <!-- URL оруулах (нэмэлт) -->
        <details style="margin-top:0.8rem">
          <summary style="font-size:0.8rem;color:var(--muted);cursor:pointer">🔗 URL-аар оруулах (нэмэлт)</summary>
          <textarea name="image_url" rows="3" style="margin-top:0.5rem;width:100%;background:var(--bg2);border:1px solid var(--border);color:var(--text);padding:0.6rem 0.9rem;border-radius:9px;font-size:0.82rem;outline:none;font-family:'Inter',sans-serif;resize:vertical" placeholder="https://example.com/img1.jpg&#10;https://example.com/img2.jpg&#10;(мөр тус бүр нэг URL)"></textarea>
        </details>
      </div>
      <!-- ====== /ЗУРАГ ====== -->

      <div class="form-group">
        <label>Тайлбар</label>
        <textarea name="description" rows="4" placeholder="Account-ын дэлгэрэнгүй мэдээлэл..."></textarea>
      </div>
      <button type="submit" class="btn-primary">🚀 НИЙТЛЭХ</button>
    </form>
  </div>

  <style>
  .upload-zone {
    border: 2px dashed var(--border);
    border-radius: 12px;
    padding: 2rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--bg2);
    position: relative;
  }
  .upload-zone:hover, .upload-zone.dragover {
    border-color: var(--accent);
    background: rgba(0,212,255,0.05);
  }
  .upload-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
  .upload-text { font-size: 0.9rem; color: var(--muted); }
  .upload-link { color: var(--accent); font-weight: 600; }
  .upload-hint { font-size: 0.75rem; color: var(--muted); margin-top: 0.3rem; }

  .img-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.6rem;
    margin-top: 0.8rem;
  }
  .preview-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 1;
    border: 1px solid var(--border);
    background: var(--bg2);
  }
  .preview-item img {
    width: 100%; height: 100%; object-fit: cover;
  }
  .preview-remove {
    position: absolute; top: 4px; right: 4px;
    background: rgba(255,82,82,0.85);
    color: #fff; border: none; border-radius: 50%;
    width: 22px; height: 22px; cursor: pointer;
    font-size: 0.75rem; display: flex; align-items: center; justify-content: center;
    line-height: 1;
  }
  .preview-badge {
    position: absolute; bottom: 4px; left: 4px;
    background: rgba(0,0,0,0.7);
    color: #fff; font-size: 0.6rem; padding: 1px 5px; border-radius: 4px;
  }
  </style>

  <script>
  let selectedFiles = [];
  const input = document.getElementById('imgInput');
  const zone  = document.getElementById('uploadZone');
  const grid  = document.getElementById('previewGrid');

  // Drag & drop
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
  });

  function handleFiles(files) {
    const arr = Array.from(files).slice(0, 5 - selectedFiles.length);
    arr.forEach(f => {
      if (selectedFiles.length >= 5) return;
      if (!f.type.startsWith('image/')) return;
      if (f.size > 5 * 1024 * 1024) { alert(f.name + ' 5MB-аас их байна.'); return; }
      selectedFiles.push(f);
    });
    rebuildInput();
    renderPreviews();
  }

  function renderPreviews() {
    grid.innerHTML = '';
    selectedFiles.forEach((f, i) => {
      const url = URL.createObjectURL(f);
      const div = document.createElement('div');
      div.className = 'preview-item';
      div.innerHTML = `
        <img src="${url}" alt="">
        <button type="button" class="preview-remove" onclick="removeFile(${i})">✕</button>
        ${i === 0 ? '<span class="preview-badge">Гол зураг</span>' : ''}
      `;
      grid.appendChild(div);
    });
  }

  function removeFile(idx) {
    selectedFiles.splice(idx, 1);
    rebuildInput();
    renderPreviews();
  }

  function rebuildInput() {
    // DataTransfer ашиглан file input-д шинэчлэх
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
  }
  </script>

<?php elseif ($tab === 'bank'): ?>
  <h2>💳 Банкны данс</h2>
  <div class="form-card" style="margin-bottom:1.5rem">
    <form method="POST">
      <input type="hidden" name="save_bank" value="1">
      <div class="form-group"><label>Банкны нэр</label><input type="text" name="bank_name" placeholder="Khan Bank" required></div>
      <div class="form-group"><label>Дансны дугаар</label><input type="text" name="account_number" placeholder="5000123456" required></div>
      <div class="form-group"><label>Дансны эзэмшигч</label><input type="text" name="account_name" placeholder="SHOP LLC" required></div>
      <button type="submit" class="btn-primary">💾 Хадгалах</button>
    </form>
  </div>
  <h3 style="font-family:'Rajdhani',sans-serif;margin-bottom:0.8rem">Одоогийн данснууд</h3>
  <?php foreach ($banks as $b): ?>
    <div style="background:var(--card);border:1px solid var(--border);border-radius:10px;padding:1rem;margin-bottom:0.7rem;font-size:0.88rem">
      <strong><?= htmlspecialchars($b['bank_name']) ?></strong> — <?= htmlspecialchars($b['account_number']) ?> (<?= htmlspecialchars($b['account_name']) ?>)
    </div>
  <?php endforeach; ?>
<?php endif; ?>

</div><!-- /main -->

<!-- CHAT PANEL -->
<?php if ($chatOrderId && $chatOrder): ?>
<div class="chat-panel" id="chatPanel">
  <div class="chat-panel-header">
    <div>
      <h4>💬 <?= htmlspecialchars($chatOrder['users']['username'] ?? '?') ?></h4>
      <div style="font-size:0.75rem;color:var(--muted)"><?= htmlspecialchars($chatOrder['accounts']['title'] ?? '') ?></div>
    </div>
    <a href="?tab=orders" class="close-chat">✕</a>
  </div>
  <div class="chat-msgs" id="cmsgs">
    <?php foreach ($chatMessages as $m): ?>
    <div class="cm <?= $m['sender_role'] ?>">
      <div style="font-size:0.68rem;color:var(--muted);margin-bottom:2px"><?= $m['sender_role']==='user'?'👤 Хэрэглэгч':($m['sender_role']==='ai'?'🤖 AI':'👨‍💼 Админ') ?></div>
      <div class="cm-bubble"><?= nl2br(htmlspecialchars($m['content'])) ?></div>
      <div class="cm-meta"><?= date('H:i', strtotime($m['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <form class="chat-send" method="POST">
    <input type="text" name="admin_msg" placeholder="Хариу бичнэ үү..." autocomplete="off">
    <button type="submit">↑</button>
  </form>
</div>
<script>document.getElementById('cmsgs').scrollTop=9999;</script>
<?php endif; ?>

</body>
</html>
