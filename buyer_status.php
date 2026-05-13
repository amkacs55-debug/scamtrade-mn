<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions WHERE buyer_id = ?");
$stmt->execute([$user_id]);
$purchases = $stmt->fetch()['total'];

echo "<!DOCTYPE html><html><head><style>body{background:#0a0a0a;color:#00ff9d;font-family:Courier New,monospace;padding:30px;}</style></head><body>";
echo "<h1>Buyer Activity</h1>";
echo "<p><strong>Total Purchases:</strong> " . $purchases . "</p>";
echo "<p>Online Status: <span style='color:#00ff00;'>● Online</span></p>";
echo "<br><a href='profile.php' style='color:#00ff9d;'>← Back</a>";
echo "</body></html>";
?>
