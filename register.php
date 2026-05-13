<?php
require_once 'includes/auth.php';
$err = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = register_user($pdo, $_POST['username']??'', $_POST['email']??'', $_POST['password']??'');
    if ($r === true) { header('Location: index.php'); exit; }
    $err = $r;
}
$pageTitle='Register'; $extraCss=['assets/css/auth.css'];
include 'includes/header.php';
?>
<div class="auth-bg">
  <div class="auth-split">
    <div class="auth-side">
      <h2>Join The <span class="accent">Vault</span></h2>
      <p>Sell your stacked accounts or score the perfect setup. Free to join.</p>
      <ul><li>List unlimited accounts</li><li>Chat with verified buyers</li><li>Track sales & favorites</li></ul>
    </div>
    <div class="auth-form">
      <h1>Create account</h1>
      <p class="sub">Get started in seconds</p>
      <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <div class="field"><label>Username</label><input name="username" required minlength="3"></div>
        <div class="field"><label>Email</label><input type="email" name="email" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" required minlength="6"></div>
        <button class="btn-primary" type="submit">Create Account</button>
      </form>
      <p class="sub" style="margin-top:14px">Already have one? <a class="accent" href="login.php">Login</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
