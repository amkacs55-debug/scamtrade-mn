<?php
require_once 'includes/auth.php';
$err = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $r = login_user($pdo, $_POST['email']??'', $_POST['password']??'');
    if ($r === true) { header('Location: index.php'); exit; }
    $err = $r;
}
$pageTitle='Login'; $extraCss=['assets/css/auth.css'];
include 'includes/header.php';
?>
<div class="auth-bg">
  <div class="auth-split">
    <div class="auth-side">
      <h2>Welcome <span class="accent">Back</span></h2>
      <p>Log in to access your vault, manage listings, and chat with buyers.</p>
      <ul>
        <li>Verified esports community</li>
        <li>Secure escrow-style transfers</li>
        <li>Real-time messaging</li>
      </ul>
    </div>
    <div class="auth-form">
      <h1>Sign in</h1>
      <p class="sub">Use your email or username</p>
      <?php if ($err): ?><div class="alert"><?= e($err) ?></div><?php endif; ?>
      <form method="post">
        <div class="field"><label>Email or Username</label><input name="email" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" required></div>
        <button class="btn-primary" type="submit">Login</button>
      </form>
      <p class="sub" style="margin-top:14px">No account? <a class="accent" href="register.php">Register here</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
