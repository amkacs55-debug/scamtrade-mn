<?php
require_once 'includes/db.php';
require_login();
$u = current_user();

$to = (int)($_GET['to'] ?? 0);
$listing = (int)($_GET['listing'] ?? 0);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $body = trim($_POST['body']??'');
    $rcv = (int)($_POST['to']??0);
    if ($body && $rcv && $rcv !== $u['id']) {
        $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,listing_id,body) VALUES (?,?,?,?)")
            ->execute([$u['id'], $rcv, $listing ?: null, $body]);
    }
    header("Location: chat.php?to=$rcv".($listing?"&listing=$listing":'')); exit;
}

// Conversations list
$convs = $pdo->prepare("
  SELECT u.id, u.username,
    (SELECT body FROM messages m WHERE (m.sender_id=u.id AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=u.id) ORDER BY m.created_at DESC LIMIT 1) AS last
  FROM users u
  WHERE u.id IN (
    SELECT IF(sender_id=?,receiver_id,sender_id) FROM messages WHERE sender_id=? OR receiver_id=?
  )
  ORDER BY u.username
");
$convs->execute([$u['id'],$u['id'],$u['id'],$u['id'],$u['id']]);
$convs = $convs->fetchAll();

$other = null; $msgs = [];
if ($to) {
    $s = $pdo->prepare("SELECT id, username FROM users WHERE id=?");
    $s->execute([$to]); $other = $s->fetch();
    if ($other) {
        $m = $pdo->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC");
        $m->execute([$u['id'],$to,$to,$u['id']]); $msgs = $m->fetchAll();
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE receiver_id=? AND sender_id=?")->execute([$u['id'],$to]);
    }
}

$pageTitle='Chat';
include 'includes/header.php';
?>
<h1 style="font-size:28px;margin-bottom:18px">Messages</h1>
<div class="chat-wrap">
  <div class="chat-list">
    <?php if (!$convs): ?><div class="item muted">No conversations yet</div>
    <?php else: foreach ($convs as $c): ?>
      <a class="item <?= $to==$c['id']?'active':'' ?>" href="chat.php?to=<?= (int)$c['id'] ?>" style="text-decoration:none;color:inherit">
        <div class="avatar" style="width:38px;height:38px;font-size:14px"><?= strtoupper(substr($c['username'],0,1)) ?></div>
        <div style="overflow:hidden">
          <div style="font-weight:700"><?= e($c['username']) ?></div>
          <div class="muted" style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($c['last']) ?></div>
        </div>
      </a>
    <?php endforeach; endif; ?>
  </div>
  <div class="chat-main">
    <?php if (!$other): ?>
      <div style="margin:auto;padding:30px" class="muted">Select a conversation, or open a listing and click "Chat with seller".</div>
    <?php else: ?>
      <div class="chat-head">
        <div class="avatar" style="width:38px;height:38px;font-size:14px"><?= strtoupper(substr($other['username'],0,1)) ?></div>
        <div><strong><?= e($other['username']) ?></strong><div class="muted" style="font-size:12px">Online</div></div>
      </div>
      <div class="chat-msgs">
        <?php foreach ($msgs as $m): ?>
          <div class="msg <?= $m['sender_id']==$u['id']?'me':'' ?>"><?= nl2br(e($m['body'])) ?></div>
        <?php endforeach; ?>
      </div>
      <form class="chat-form" method="post">
        <input type="hidden" name="to" value="<?= (int)$other['id'] ?>">
        <?php if ($listing): ?><input type="hidden" name="listing" value="<?= $listing ?>"><?php endif; ?>
        <input name="body" placeholder="Type a message…" required>
        <button type="submit">Send</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script src="assets/js/chat.js"></script>
<?php include 'includes/footer.php'; ?>
