<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle).' — GameVault' : 'GameVault — Premium Gaming Marketplace' ?></title>
<meta name="description" content="GameVault — buy and sell Mobile Legends, PUBG Mobile, and Standoff2 accounts. Secure esports marketplace.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<?php if (!empty($extraCss)) foreach ($extraCss as $c): ?>
<link rel="stylesheet" href="<?= e($c) ?>">
<?php endforeach; ?>
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<main class="main">
