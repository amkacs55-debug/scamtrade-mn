<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Нэвтрэх нэр эсвэл нууц үг буруу байна.";
        }
    } catch (PDOException $e) {
        $error = "Алдаа: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Нэвтрэх · SMM Store</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#020408;--card:#070e1a;--pr:#00f0ff;--pr2:#0066ff;--ac:#00ff88;--rd:#ff3366;--tx:#c8e8ff;--sub:#4a7090;--bd:rgba(0,240,255,0.1)}
body{background:var(--bg);color:var(--tx);font-family:'Space Grotesk',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}
body::before{content:'';position:fixed;inset:0;background:linear-gradient(rgba(0,240,255,0.013) 1px,transparent 1px),linear-gradient(90deg,rgba(0,240,255,0.013) 1px,transparent 1px);background-size:40px 40px;pointer-events:none}
body::after{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(0,102,255,0.13),transparent);pointer-events:none}

.wrap{width:100%;max-width:360px;position:relative;z-index:1}

/* Logo */
.logo{text-align:center;margin-bottom:30px}
.logo-icon{width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,var(--pr2),var(--pr));display:flex;align-items:center;justify-content:center;margin:0 auto 12px;box-shadow:0 0 32px rgba(0,240,255,0.25);font-size:24px;color:#020408}
.logo-text{font-family:'Orbitron',sans-serif;font-weight:900;font-size:19px;background:linear-gradient(135deg,var(--pr),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent;letter-spacing:2px}
.logo-sub{font-size:11px;color:var(--sub);margin-top:4px;letter-spacing:1px;text-transform:uppercase}

/* Card */
.card{background:var(--card);border:1px solid var(--bd);border-radius:22px;padding:28px;position:relative;overflow:hidden}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,rgba(0,240,255,0.45),transparent)}

.card-title{font-family:'Orbitron',sans-serif;font-size:18px;font-weight:700;margin-bottom:22px;text-align:center}

/* Input */
.field{margin-bottom:14px}
.field-label{display:block;font-size:11px;color:var(--sub);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-weight:600}
.inp-wrap{position:relative}
.inp-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--sub);font-size:13px;pointer-events:none}
.finp{width:100%;background:rgba(0,240,255,0.03);border:1px solid rgba(0,240,255,0.1);border-radius:13px;padding:14px 14px 14px 40px;color:var(--tx);font-family:'Space Grotesk',sans-serif;font-size:14px;outline:none;transition:0.2s}
.finp:focus{border-color:rgba(0,240,255,0.38);background:rgba(0,240,255,0.05);box-shadow:0 0 14px rgba(0,240,255,0.05)}
.finp::placeholder{color:var(--sub)}

/* Forgot password */
.forgot{display:block;text-align:right;font-size:12px;color:var(--sub);text-decoration:none;margin-top:6px;margin-bottom:20px;transition:0.2s}
.forgot:hover{color:var(--pr)}

/* Button */
.btn{width:100%;padding:15px;background:linear-gradient(135deg,var(--pr2),var(--pr));color:#020408;border:none;border-radius:14px;font-family:'Orbitron',sans-serif;font-weight:900;font-size:14px;letter-spacing:1.5px;cursor:pointer;transition:0.2s;margin-bottom:18px}
.btn:hover{opacity:0.9;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,240,255,0.2)}
.btn:active{transform:scale(0.98)}

/* Error */
.alert-err{background:rgba(255,51,102,0.07);color:var(--rd);border:1px solid rgba(255,51,102,0.2);border-radius:12px;padding:12px 14px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}

/* Register link */
.reg-row{text-align:center;font-size:13px;color:var(--sub)}
.reg-row a{color:var(--pr);text-decoration:none;font-weight:700}
.reg-row a:hover{text-decoration:underline}

/* Divider */
.divider{display:flex;align-items:center;gap:10px;margin-bottom:16px}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(0,240,255,0.08)}
.divider span{font-size:11px;color:var(--sub);letter-spacing:1px;text-transform:uppercase}
</style>
</head>
<body>
<div class="wrap">

  <div class="logo">
    <div class="logo-icon"><i class="fas fa-bolt"></i></div>
    <div class="logo-text">SMM·STORE</div>
    <div class="logo-sub">Social Media Marketing</div>
  </div>

  <div class="card">
    <div class="card-title">Нэвтрэх</div>

    <?php if($error): ?>
    <div class="alert-err"><i class="fas fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="field">
        <label class="field-label">Нэвтрэх нэр / Имэйл</label>
        <div class="inp-wrap">
          <i class="fas fa-user inp-ico"></i>
          <input type="text" name="username" class="finp" placeholder="Нэр эсвэл имэйл" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
      </div>

      <div class="field">
        <label class="field-label">Нууц үг</label>
        <div class="inp-wrap">
          <i class="fas fa-lock inp-ico"></i>
          <input type="password" name="password" class="finp" placeholder="••••••••" required>
        </div>
      </div>

      <a href="forget-password.php" class="forgot"><i class="fas fa-key" style="margin-right:4px"></i>Нууц үг мартсан?</a>

      <button type="submit" class="btn"><i class="fas fa-right-to-bracket" style="margin-right:8px"></i>НЭВТРЭХ</button>
    </form>

    <div class="divider"><span>эсвэл</span></div>
    <div class="reg-row">Бүртгэлгүй юу? <a href="register.php">Бүртгүүлэх</a></div>
  </div>

</div>
</body>
</html>
