<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $error = "Username or email already exists";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hash])) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Registration failed";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - GameVault</title>
    <style>
        body { background: #0a0a0a; color: #00ff9d; font-family: 'Courier New', monospace; margin:0; }
        .container { max-width: 400px; margin: 100px auto; padding: 30px; background: #111; border: 2px solid #00ff9d; border-radius: 10px; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #1a1a1a; border: 1px solid #00ff9d; color: #fff; }
        button { width: 100%; padding: 12px; background: #00ff9d; color: #000; border: none; font-weight: bold; cursor: pointer; }
        .neon { text-shadow: 0 0 10px #00ff9d; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="neon">CREATE ACCOUNT</h1>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">REGISTER</button>
    </form>
    <p><a href="login.php" style="color:#00ff9d;">Already have account? Login</a></p>
</div>
</body>
</html>
