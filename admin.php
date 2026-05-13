<?php
require_once 'includes/db.php';
require_admin();

if (isset($_GET['approve'])) { $pdo->prepare("UPDATE listings SET status='approved' WHERE id=?")->execute([(int)$_GET['approve']]); header('Location: admin.php'); exit; }
if (isset($_GET['reject']))  { $pdo->prepare("UPDATE listings SET status='rejected' WHERE id=?")->execute([(int)$_GET['reject']]);  header('Location: admin.php'); exit; }
if (isset($_GET['del_listing'])) { $pdo->prepare("DELETE FROM listings WHERE id=?")->execute([(int)$_GET['del_listing']]); header('Location: admin.php'); exit; }
if (isset($_GET['del_user'])) { $pdo->prepare("DELETE FROM users WHERE id=? AND is_admin=0")->execute([(int)$_GET['del_user']]); header('Location: admin.php'); exit; }

$users = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$listings = (int)$pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$pending = (int)$pdo->query("SELECT COUNT(*) FROM listings WHERE status='pending'")->fetchColumn();
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE status='completed'")->fetchColumn();

$recentListings = $pdo->query("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id ORDER BY l.created_at DESC LIMIT 8")->fetchAll();
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Chart data: listings by game
$byGame = $pdo->query("SELECT game, COUNT(*) c FROM listings GROUP BY game")->fetchAll();
$labels = array_map(fn($r)=>$r['game'],$byGame);
$values = array_map(fn($r)=>(int)$r['c'],$byGame);

// Revenue last 7 days
$rev = $pdo->query("SELECT DATE(created_at) d, COALESCE(SUM(amount),0) v FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY d ORDER BY d")->fetchAll();

$pageTitle='Admin'; $extraCss=['assets/css/dashboard.css'];
include 'includes/header.php';
?>
<div class="dash-head">
  <div>
    <h1 style="font-size:30px">Admin <span class="accent">Dashboard</span></h1>
    <p class="muted">Operational overview & moderation</p>
  </div>
</div>

<div class="admin-grid">
  <div class="kpi"><div class="lbl">Total Users</div><div class="val"><?= number_format($users) ?></div></div>
  <div class="kpi"><div class="lbl">Total Listings</div><div class="val"><?= number_format($listings) ?></div></div>
  <div class="kpi"><div class="lbl">Pending Review</div><div class="val"><?= number_format($pending) ?></div></div>
  <div class="kpi"><div class="lbl">Revenue</div><div class="val">$<?= number_format($revenue,2) ?></div></div>
</div>

<div class="layout" style="grid-template-columns:1fr 1fr">
  <div class="chart-card"><h3 style="margin-bottom:10px">Listings by Game</h3><canvas id="chart1" height="160"></canvas></div>
  <div class="chart-card"><h3 style="margin-bottom:10px">Revenue (7 days)</h3><canvas id="chart2" height="160"></canvas></div>
</div>

<section class="section">
  <h2 style="margin-bottom:14px">Recent Listings</h2>
  <table class="table">
    <thead><tr><th>Title</th><th>Seller</th><th>Game</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($recentListings as $l): ?>
        <tr>
          <td><a href="product.php?id=<?= (int)$l['id'] ?>"><?= e($l['title']) ?></a></td>
          <td><?= e($l['username']) ?></td>
          <td><?= e($l['game']) ?></td>
          <td>$<?= number_format($l['price'],2) ?></td>
          <td><?= e($l['status']) ?></td>
          <td>
            <a class="btn-sm btn-approve" href="?approve=<?= (int)$l['id'] ?>">Approve</a>
            <a class="btn-sm btn-reject" href="?reject=<?= (int)$l['id'] ?>">Reject</a>
            <a class="btn-sm btn-delete" href="?del_listing=<?= (int)$l['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<section class="section">
  <h2 style="margin-bottom:14px">Recent Users</h2>
  <table class="table">
    <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($recentUsers as $u): ?>
        <tr>
          <td><?= e($u['username']) ?></td><td><?= e($u['email']) ?></td>
          <td><?= $u['is_admin']?'<span class="badge-admin">Admin</span>':'User' ?></td>
          <td><?= e($u['created_at']) ?></td>
          <td>
            <?php if (!$u['is_admin']): ?>
              <a class="btn-sm btn-delete" href="?del_user=<?= (int)$u['id'] ?>" onclick="return confirm('Delete user?')">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const c1 = document.getElementById('chart1');
new Chart(c1, {
  type:'doughnut',
  data:{labels: <?= json_encode($labels) ?>, datasets:[{data: <?= json_encode($values) ?>, backgroundColor:['#22d3ee','#a855f7','#ec4899'], borderWidth:0}]},
  options:{plugins:{legend:{labels:{color:'#e7ecff'}}}}
});
const c2 = document.getElementById('chart2');
new Chart(c2, {
  type:'line',
  data:{
    labels: <?= json_encode(array_map(fn($r)=>$r['d'],$rev)) ?>,
    datasets:[{label:'USD', data: <?= json_encode(array_map(fn($r)=>(float)$r['v'],$rev)) ?>, borderColor:'#22d3ee', backgroundColor:'rgba(168,85,247,.2)', tension:.35, fill:true}]
  },
  options:{plugins:{legend:{labels:{color:'#e7ecff'}}}, scales:{x:{ticks:{color:'#8a93b8'}}, y:{ticks:{color:'#8a93b8'}}}}
});
</script>
<?php include 'includes/footer.php'; ?>
