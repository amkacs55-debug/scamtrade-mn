<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        .profile-card { background:#111; border:2px solid #00ff9d; max-width:600px; margin:auto; padding:30px; border-radius:12px; }
    </style>
</head>
<body>
<div class="profile-card">
    <h1>Your Profile</h1>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Balance:</strong> <?= number_format($user['balance'], 2) ?>₮</p>
    <a href="logout.php" style="color:red;">Logout</a>
</div>
</body>
</html>
