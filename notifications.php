<?php
require_once 'includes/db.php';
require_login();
$u = current_user();

if (isset($_GET['read'])) {
    $pdo->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?')->execute([(int)$_GET['read'], $u['id']]);
    header('Location: notifications.php'); exit;
}

$rows = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$rows->execute([$u['id']]); $rows = $rows->fetchAll();

$pageTitle='Notifications';
include 'includes/header.php';
?>
<h1 style="font-size:30px;margin-bottom:20px">Notifications</h1>
<?php if (!$rows): ?><p class="muted">You're all caught up.</p>
<?php else: foreach ($rows as $n): ?>
  <div class="notif-item <?= $n['is_read']?'':'unread' ?>">
    <div>
      <strong><?= e($n['title']) ?></strong>
      <p class="muted" style="margin-top:4px"><?= e($n['body']) ?></p>
      <small class="muted"><?= e($n['created_at']) ?></small>
    </div>
    <?php if (!$n['is_read']): ?><a class="btn-ghost" href="?read=<?= (int)$n['id'] ?>">Mark read</a><?php endif; ?>
  </div>
<?php endforeach; endif; ?>
<?php include 'includes/footer.php'; ?>
