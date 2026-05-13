<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = (int)$_POST['listing_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $seller_id = (int)$_POST['seller_id'];

    $stmt = $pdo->prepare("INSERT INTO reviews (listing_id, reviewer_id, seller_id, rating, comment) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$listing_id, $_SESSION['user_id'], $seller_id, $rating, $comment]);
    $success = "Review submitted successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Review</title>
    <style>
        body { background:#0a0a0a; color:#00ff9d; font-family:'Courier New',monospace; padding:20px; }
        .form-box { max-width:600px; margin:auto; background:#111; padding:40px; border:2px solid #00ff9d; border-radius:12px; }
        select, textarea, button { width:100%; padding:12px; margin:10px 0; background:#1a1a1a; border:1px solid #00ff9d; color:white; }
        button { background:#00ff9d; color:black; font-weight:bold; cursor:pointer; }
    </style>
</head>
<body>
<div class="form-box">
    <h1>⭐ Leave a Review</h1>
    <?php if(isset($success)) echo "<p style='color:#00ff9d;'>$success</p>"; ?>
    
    <form method="POST">
        <input type="hidden" name="listing_id" value="<?= $_GET['listing_id'] ?? '' ?>">
        <input type="hidden" name="seller_id" value="<?= $_GET['seller_id'] ?? '' ?>">
        
        <select name="rating" required>
            <option value="">Select Rating</option>
            <option value="5">5 ★ Excellent</option>
            <option value="4">4 ★ Good</option>
            <option value="3">3 ★ Average</option>
            <option value="2">2 ★ Poor</option>
            <option value="1">1 ★ Bad</option>
        </select>
        
        <textarea name="comment" rows="6" placeholder="Write your review here..." required></textarea>
        <button type="submit">SUBMIT REVIEW</button>
    </form>
</div>
</body>
</html>
