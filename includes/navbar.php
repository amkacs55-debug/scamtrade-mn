<?php require_once __DIR__ . '/db.php'; $u = current_user(); ?>
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="logo">
      <span class="logo-mark">▲</span>
      <span class="logo-text">GAME<span class="accent">VAULT</span></span>
    </a>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="marketplace.php">Marketplace</a></li>
      <li><a href="sell.php">Sell</a></li>
      <?php if ($u): ?>
        <li><a href="chat.php">Chat</a></li>
        <li><a href="favorites.php">Favorites</a></li>
        <li><a href="notifications.php">Notifications</a></li>
        <li><a href="profile.php" class="user-chip"><?= e($u['username']) ?></a></li>
        <?php if (!empty($u['is_admin'])): ?><li><a href="admin.php" class="badge-admin">Admin</a></li><?php endif; ?>
        <li><a href="logout.php" class="btn-ghost">Logout</a></li>
      <?php else: ?>
        <li><a href="login.php" class="btn-ghost">Login</a></li>
        <li><a href="register.php" class="btn-glow">Get Started</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
