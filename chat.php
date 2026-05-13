<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

// Get all chats for current user
$stmt = $pdo->prepare("SELECT c.*, u1.username as user1_name, u2.username as user2_name 
                      FROM chats c 
                      LEFT JOIN users u1 ON c.user1_id = u1.id 
                      LEFT JOIN users u2 ON c.user2_id = u2.id 
                      WHERE c.user1_id = ? OR c.user2_id = ?");
$stmt->execute([$user_id, $user_id]);
$chats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        .chat-list { max-width:800px; margin:auto; }
        .chat-item { background:#111; border:1px solid #00ff9d; padding:15px; margin:10px 0; border-radius:8px; cursor:pointer; }
        .chat-item:hover { background:#1a1a1a; }
    </style>
</head>
<body>
<div class="chat-list">
    <h1 class="neon">MESSAGES</h1>
    <?php if(empty($chats)): ?>
        <p>No conversations yet. Start chatting from listings!</p>
    <?php else: ?>
        <?php foreach($chats as $chat): ?>
            <div class="chat-item" onclick="window.location='chat.php?chat_id=<?= $chat['id'] ?>'">
                <strong>
                    <?= htmlspecialchars($chat['user1_id'] == $user_id ? $chat['user2_name'] : $chat['user1_name']) ?>
                </strong>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
