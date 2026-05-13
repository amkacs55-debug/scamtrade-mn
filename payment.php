<?php
require_once 'includes/db.php';
require_login();
$u = current_user();
$id = (int)($_GET['listing'] ?? 0);
$stmt = $pdo->prepare("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id WHERE l.id=?");
$stmt->execute([$id]);
$l = $stmt->fetch();
if (!$l) { http_response_code(404); echo 'Not found'; exit; }

$success = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pdo->prepare("INSERT INTO transactions (buyer_id,seller_id,listing_id,amount,status) VALUES (?,?,?,?, 'completed')")
        ->execute([$u['id'], $l['user_id'], $l['id'], $l['price']]);
    $pdo->prepare("UPDATE listings SET status='sold' WHERE id=?")->execute([$l['id']]);
    $pdo->prepare("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)")
        ->execute([$l['user_id'],'Account sold!','Your listing "'.$l['title'].'" was just purchased.']);
    $success = true;
}

$pageTitle='Checkout';
include 'includes/header.php';
?>
<div class="form-card" style="max-width:560px">
  <h1>Secure <span class="accent">Checkout</span></h1>
  <p class="sub"><?= e($l['title']) ?></p>
  <?php if ($success): ?>
    <div class="alert success">Payment successful! Check your notifications and the seller will contact you.</div>
    <a class="btn-primary" href="profile.php" style="display:block;text-align:center;text-decoration:none">Go to profile</a>
  <?php else: ?>
    <div class="card" style="padding:18px;margin-bottom:18px;display:flex;justify-content:space-between"><span>Total</span><strong style="color:var(--neon-blue);font-family:Orbitron">$<?= number_format($l['price'],2) ?></strong></div>
    <form method="post">
      <div class="field"><label>Cardholder Name</label><input required placeholder="Demo Mode"></div>
      <div class="field"><label>Card Number</label><input required placeholder="4242 4242 4242 4242"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div class="field"><label>Expiry</label><input required placeholder="12/28"></div>
        <div class="field"><label>CVC</label><input required placeholder="123"></div>
      </div>
      <button class="btn-primary" type="submit">Pay $<?= number_format($l['price'],2) ?></button>
    </form>
    <p class="muted" style="text-align:center;margin-top:10px;font-size:12px">Demo checkout — no real payment is processed.</p>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
