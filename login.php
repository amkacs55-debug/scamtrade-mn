<?php
// login.php
require_once 'config.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $res = sb_get('users', 'email=eq.' . urlencode($email) . '&select=*');
        $users = $res['data'] ?? [];

        if (!empty($users) && password_verify($password, $users[0]['password_hash'])) {
            $user = $users[0];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: ' . ($user['role'] === 'admin' ? 'dashboard_admin.php' : 'index.php'));
            exit;
        } else {
            $error = 'Имэйл эсвэл нууц үг буруу байна.';
        }
    } else {
        $error = 'Бүх талбарыг бөглөнө үү.';
    }
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Нэвтрэх — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root { --bg:#050810;--card:#0d1428;--border:#1a2545;--accent:#00d4ff;--text:#e8eaf6;--muted:#6b7a99;--danger:#ff5252; }
* { margin:0;padding:0;box-sizing:border-box; }
body { background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1rem; background-image: radial-gradient(ellipse 60% 50% at 50% -10%, rgba(0,212,255,0.1), transparent); }
.logo { font-family:'Rajdhani',sans-serif;font-size:2rem;font-weight:700;background:linear-gradient(135deg,#00d4ff,#ff6b35);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-decoration:none;margin-bottom:2rem;display:block;text-align:center; }
.auth-box { background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2.5rem;width:100%;max-width:420px; }
.auth-box h2 { font-family:'Rajdhani',sans-serif;font-size:1.8rem;font-weight:700;margin-bottom:0.3rem; }
.auth-box p { color:var(--muted);font-size:0.88rem;margin-bottom:2rem; }
.form-group { margin-bottom:1.2rem; }
label { display:block;font-size:0.82rem;color:var(--muted);margin-bottom:0.4rem;font-weight:500; }
input { width:100%;background:#080d1c;border:1px solid var(--border);color:var(--text);padding:0.7rem 1rem;border-radius:10px;font-size:0.92rem;outline:none;transition:border-color 0.2s;font-family:'Inter',sans-serif; }
input:focus { border-color:var(--accent); }
.btn-full { width:100%;padding:0.8rem;background:linear-gradient(135deg,#00d4ff,#0099cc);color:#000;font-weight:700;font-size:1rem;border:none;border-radius:10px;cursor:pointer;font-family:'Rajdhani',sans-serif;letter-spacing:0.5px;margin-top:0.5rem;transition:opacity 0.2s; }
.btn-full:hover { opacity:0.85; }
.error { background:rgba(255,82,82,0.1);border:1px solid var(--danger);color:var(--danger);padding:0.7rem 1rem;border-radius:8px;font-size:0.85rem;margin-bottom:1rem; }
.alt-link { text-align:center;margin-top:1.5rem;font-size:0.85rem;color:var(--muted); }
.alt-link a { color:var(--accent);text-decoration:none; }
</style>
</head>
<body>
<a href="index.php" class="logo">⚔ ML&PUBG Shop</a>
<div class="auth-box">
  <h2>Нэвтрэх</h2>
  <p>Таны account руу нэвтэрнэ үү</p>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Имэйл</label>
      <input type="email" name="email" placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Нууц үг</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-full">НЭВТРЭХ</button>
  </form>
  <div class="alt-link">Бүртгэл байхгүй юу? <a href="register.php">Бүртгүүлэх</a></div>
</div>
</body>
</html>
