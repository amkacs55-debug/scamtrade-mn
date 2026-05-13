<?php
require_once 'includes/db.php';
require_login();
$pageTitle='Sell Account';
$msg = null; $err = null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $title = trim($_POST['title']??'');
    $game  = $_POST['game']??'';
    $rank  = trim($_POST['rank']??'');
    $skins = (int)($_POST['skins']??0);
    $price = (float)($_POST['price']??0);
    $desc  = trim($_POST['description']??'');
    $img   = trim($_POST['image_url']??'');

    if (!$title || !$game || !$price) $err = 'Title, game and price are required.';
    elseif (!in_array($game,['Mobile Legends','PUBG Mobile','Standoff2'])) $err = 'Invalid game.';
    else {
        if (!empty($_FILES['image']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp','gif'])) {
                $name = 'uploads/'.uniqid('acct_').'.'.$ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__.'/'.$name)) $img = $name;
            }
        }
        $stmt = $pdo->prepare("INSERT INTO listings (user_id,title,game,rank_name,skin_count,price,description,image,status) VALUES (?,?,?,?,?,?,?,?, 'approved')");
        $stmt->execute([current_user()['id'],$title,$game,$rank,$skins,$price,$desc,$img]);
        header('Location: product.php?id='.$pdo->lastInsertId()); exit;
    }
}
include 'includes/header.php';
?>
<div class="form-card" style="max-width:680px">
  <h1>List Your <span class="accent">Account</span></h1>
  <p class="sub">Reach thousands of buyers in minutes.</p>
  <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="field"><label>Title</label><input name="title" required placeholder="e.g. Mythic Glory ML — 250 Skins"></div>
    <div class="field"><label>Game</label>
      <select name="game" required>
        <option value="">Select game</option>
        <option>Mobile Legends</option><option>PUBG Mobile</option><option>Standoff2</option>
      </select>
    </div>
    <div class="field"><label>Rank</label><input name="rank" placeholder="e.g. Mythic, Conqueror, Legendary"></div>
    <div class="field"><label>Skin Count</label><input type="number" name="skins" min="0" value="0"></div>
    <div class="field"><label>Price (USD)</label><input type="number" step="0.01" name="price" required placeholder="99.00"></div>
    <div class="field"><label>Description</label><textarea name="description" rows="6" placeholder="Account details, skins, transfer method…"></textarea></div>
    <div class="field"><label>Image URL (optional)</label><input name="image_url" placeholder="https://…"></div>
    <div class="field"><label>Or upload image</label><input type="file" name="image" accept="image/*"></div>
    <button class="btn-primary" type="submit">Publish Listing</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
