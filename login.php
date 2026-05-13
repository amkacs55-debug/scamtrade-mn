<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GameVault</title>
    <style>
        body { background: #0a0a0a; color: #00ff9d; font-family: 'Courier New', monospace; }
        .container { max-width: 400px; margin: 100px auto; padding: 30px; background: #111; border: 2px solid #00ff9d; border-radius: 10px; }
        input, button { width: 100%; padding: 12px; margin: 10px 0; }
        input { background: #1a1a1a; border: 1px solid #00ff9d; color: white; }
        button { background: #00ff9d; color: black; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="neon">LOGIN</h1>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>
    <p><a href="register.php" style="color:#00ff9d;">Create new account</a></p>
</div>
</body>
</html>
