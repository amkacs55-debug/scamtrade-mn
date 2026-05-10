<?php
include 'config.php';
$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($username) < 3) {
        $error = "Хэрэглэгчийн нэр дор хаяж 3 тэмдэгт байх ёстой.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Имэйл хаяг буруу байна.";
    } elseif (strlen($password) < 6) {
        $error = "Нууц үг дор хаяж 6 тэмдэгт байх ёстой.";
    } elseif ($password !== $confirm) {
        $error = "Нууц үг таарахгүй байна.";
    } else {
        $generated_id = "#" . substr(md5(uniqid()), 0, 8);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("INSERT INTO profiles (id, username, email, password, balance) VALUES (?, ?, ?, ?, 0.00)");
            $stmt->execute([$generated_id, $username, $email, $hashed]);
            $success = true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'duplicate') !== false || strpos($e->getMessage(), 'unique') !== false) {
                $error = "Энэ нэр эсвэл имэйл аль хэдийн бүртгэлтэй байна.";
            } else {
                $error = "Алдаа гарлаа: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Бүртгүүлэх · SMM Store</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#020408;--card:#070e1a;--pr:#00f0ff;--pr2:#0066ff;--ac:#00ff88;--rd:#ff3366;--tx:#c8e8ff;--sub:#4a7090;--bd:rgba(0,240,255,0.1)}
body{background:var(--bg);color:var(--tx);font-family:'Space Grotesk',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}
body::before{content:'';position:fixed;inset:0;background:linear-gradient(rgba(0,240,255,0.013) 1px,transparent 1px),linear-gradient(90deg,rgba(0,240,255,0.013) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
body::after{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(0,102,255,0.13),transparent);pointer-events:none}
.wrap{width:100%;max-width:360px;position:relative;z-index:1}
.logo{text-align:center;margin-bottom:24px}
.logo-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--pr2),var(--pr));display:flex;align-items:center;justify-content:center;margin:0 auto 10px;box-shadow:0 0 30px rgba(0,240,255,0.25);font-size:22px;color:#020408}
.logo-text{font-family:'Orbitron',sans-serif;font-weight:900;font-size:18px;background:linear-gradient(135deg,var(--pr),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:2px}
.logo-sub{font-size:11px;color:var(--sub);margin-top:4px;letter-spacing:1px;text-transform:uppercase}
.card{background:var(--card);border:1px solid var(--bd);border-radius:22px;padding:26px;position:relative;overflow:hidden}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,240,255,0.45),transparent)}
.card-title{font-family:'Orbitron',sans-serif;font-size:17px;font-weight:700;margin-bottom:20px;text-align:center}
.field{margin-bottom:13px}
.field-label{display:block;font-size:11px;color:var(--sub);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;font-weight:600}
.inp-wrap{position:relative}
.inp-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--sub);font-size:13px;pointer-events:none}
.finp{width:100%;background:rgba(0,240,255,0.03);border:1px solid rgba(0,240,255,0.1);border-radius:13px;padding:13px 13px 13px 40px;color:var(--tx);font-family:'Space Grotesk',sans-serif;font-size:14px;outline:none;transition:0.2s}
.finp:focus{border-color:rgba(0,240,255,0.38);background:rgba(0,240,255,0.05)}
.finp::placeholder{color:var(--sub)}
.btn{width:100%;padding:15px;background:linear-gradient(135deg,var(--pr2),var(--pr));color:#020408;border:none;border-radius:14px;font-family:'Orbitron',sans-serif;font-weight:900;font-size:14px;letter-spacing:1.5px;cursor:pointer;transition:0.2s;margin-top:4px}
.btn:active{transform:scale(0.98)}
.alert-err{background:rgba(255,51,102,0.07);color:var(--rd);border:1px solid rgba(255,51,102,0.2);border-radius:12px;padding:12px 14px;font-size:13px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.alert-ok{background:rgba(0,255,136,0.07);color:var(--ac);border:1px solid rgba(0,255,136,0.2);border-radius:12px;padding:16px;font-size:13px;margin-bottom:14px;text-align:center}
.divider{display:flex;align-items:center;gap:10px;margin:16px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(0,240,255,0.08)}
.divider span{font-size:11px;color:var(--sub);letter-spacing:1px;text-transform:uppercase}
.login-row{text-align:center;font-size:13px;color:var(--sub)}
.login-row a{color:var(--pr);text-decoration:none;font-weight:700}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
  </div>
  <div class="card">
    <div class="card-title">Бүртгүүлэх</div>

    <?php if($error): ?>
    <div class="alert-err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="alert-ok">
      <i class="fas fa-check-circle" style="font-size:20px;display:block;margin-bottom:8px"></i>
      Амжилттай бүртгүүллээ!<br>
      <a href="login.php" style="color:var(--pr);font-weight:700;text-decoration:none">Нэвтрэх →</a>
    </div>
    <?php else: ?>
    <form method="POST" autocomplete="off">
      <div class="field">
        <label class="field-label">Хэрэглэгчийн нэр</label>
        <div class="inp-wrap">
          <i class="fas fa-user inp-ico"></i>
          <input type="text" name="username" class="finp" placeholder="username" required value="<?= htmlspecialchars($_POST['username']??'') ?>">
        </div>
      </div>
      <div class="field">
        <label class="field-label">Имэйл хаяг</label>
        <div class="inp-wrap">
          <i class="fas fa-envelope inp-ico"></i>
          <input type="email" name="email" class="finp" placeholder="name@example.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
        </div>
      </div>
      <div class="field">
        <label class="field-label">Нууц үг</label>
        <div class="inp-wrap">
          <i class="fas fa-lock inp-ico"></i>
          <input type="password" name="password" class="finp" placeholder="••••••••" required>
        </div>
      </div>
      <div class="field">
        <label class="field-label">Нууц үг давтах</label>
        <div class="inp-wrap">
          <i class="fas fa-lock inp-ico"></i>
          <input type="password" name="confirm" class="finp" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn"><i class="fas fa-user-plus" style="margin-right:8px"></i>БҮРТГҮҮЛЭХ</button>
    </form>
    <?php endif; ?>

    <div class="divider"><span>эсвэл</span></div>
    <div class="login-row">Аккаунттай юу? <a href="login.php">Нэвтрэх</a></div>
  </div>
</div>
</body>
</html>
