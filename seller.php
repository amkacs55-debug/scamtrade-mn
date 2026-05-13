<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $game = $_POST['game'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);
    $level = (int)$_POST['level'];
    $type = $_POST['type']; // sell or rent

    // Basic image handling (save as base64 or path - simplified)
    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        // For production use proper upload with validation
        $images[] = "uploads/" . time() . "_" . $_FILES['images']['name'][0]; // placeholder
    }

    $stmt = $pdo->prepare("INSERT INTO listings 
        (seller_id, game, title, description, price, listing_type, account_level, images) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $img_array = $images ? '{' . implode(',', $images) . '}' : '{}';
    $stmt->execute([$_SESSION['user_id'], $game, $title, $description, $price, $type, $level, $img_array]);

    $success = "Listing created successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Account</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        form { max-width:700px; margin:auto; background:#111; padding:30px; border:2px solid #00ff9d; border-radius:10px; }
        input, select, textarea { width:100%; padding:12px; margin:10px 0; background:#1a1a1a; border:1px solid #00ff9d; color:white; }
        button { background:#00ff9d; color:black; padding:15px; font-weight:bold; border:none; cursor:pointer; width:100%; }
    </style>
</head>
<body>
<div>
    <h1 class="neon">SELL / RENT ACCOUNT</h1>
    <?php if(isset($success)) echo "<p style='color:#00ff9d;'>$success</p>"; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <select name="game" required>
            <option value="Standoff 2">Standoff 2</option>
            <option value="Mobile Legends">Mobile Legends</option>
            <option value="PUBG Mobile">PUBG Mobile</option>
        </select>
        
        <input type="text" name="title" placeholder="Title (e.g. Legendary Account)" required>
        <textarea name="description" rows="5" placeholder="Description + skins/items" required></textarea>
        
        <input type="number" name="price" placeholder="Price in ₮" step="0.01" required>
        <input type="number" name="level" placeholder="Account Level" required>
        
        <select name="type" required>
            <option value="sell">Sell</option>
            <option value="rent">Rent</option>
        </select>
        
        <input type="file" name="images[]" accept="image/*">
        
        <button type="submit">POST LISTING</button>
    </form>
</div>
</body>
</html>
