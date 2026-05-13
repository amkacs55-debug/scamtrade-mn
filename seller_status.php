<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

// Check verified status
$stmt = $pdo->prepare("SELECT * FROM verified_sellers WHERE user_id = ? AND expires_at > NOW()");
$stmt->execute([$user_id]);
$verified = $stmt->fetch();

echo "<!DOCTYPE html><html><head><style>body{background:#0a0a0a;color:#00ff9d;font-family:Courier New,monospace;padding:30px;}</style></head><body>";
echo "<h1>Seller Status</h1>";
echo "<p><strong>Username:</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";

if ($verified) {
    echo "<h2 style='color:#ff00ff;'>✅ VERIFIED SELLER (Active)</h2>";
    echo "<p>Expires: " . $verified['expires_at'] . "</p>";
} else {
    echo "<h2>❌ Not Verified</h2>";
    echo "<a href='verified_seller.php' style='color:#ff00ff;'>→ Become Verified Seller</a>";
}

echo "<br><a href='profile.php' style='color:#00ff9d;'>← Back to Profile</a>";
echo "</body></html>";
?>
