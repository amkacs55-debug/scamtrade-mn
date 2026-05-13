<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        // In real project send reset email. Here we simulate.
        $success = "Password reset link has been sent to your email (simulation).";
    } else {
        $error = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; }
        .container { max-width:400px; margin:100px auto; padding:30px; background:#111; border:2px solid #00ff9d; border-radius:10px; }
        input, button { width:100%; padding:12px; margin:10px 0; }
        input { background:#1a1a1a; border:1px solid #00ff9d; color:white; }
        button { background:#00ff9d; color:black; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Forgot Password</h1>
    <?php if(isset($success)) echo "<p style='color:#00ff9d;'>$success</p>"; ?>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">SEND RESET LINK</button>
    </form>
    <p><a href="login.php" style="color:#00ff9d;">← Back to Login</a></p>
</div>
</body>
</html>
