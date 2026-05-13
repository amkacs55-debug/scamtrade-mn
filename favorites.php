<?php
require_once 'includes/db.php';
require_login();
$u = current_user();

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $exists = $pdo->prepare('SELECT id FROM favorites WHERE user_id=? AND listing_id=?');
    $exists->execute([$u['id'],$id]);
    if ($exists->fetch()) $pdo->prepare('DELETE FROM favorites WHERE user_id=? AND listing_id=?')->execute([$u['id'],$id]);
    else $pdo->prepare('INSERT INTO favorites (user_id,listing_id) VALUES (?,?)')->execute([$u['id'],$id]);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT']??'','json')!==false) { echo 'ok'; exit; }
    header('Location: favorites.php'); exit;
}

$favs = $pdo->prepare("SELECT l.*, u.username FROM favorites f JOIN listings l ON l.id=f.listing_id JOIN users u ON u.id=l.user_id WHERE f.user_id=? ORDER BY f.created_at DESC");
$favs->execute([$u['id']]); $favs = $favs->fetchAll();

$pageTitle='Favorites';
include 'includes/header.php';
?>
<h1 style="font-size:30px;margin-bottom:20px">Your <span class="accent">Favorites</span></h1>
<div class="grid">
  <?php if (!$favs): ?><p class="muted">No favorites yet — start exploring the marketplace.</p>
  <?php else: foreach ($favs as $l) include 'includes/_card.php'; endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
