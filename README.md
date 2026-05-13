# GameVault — Premium Gaming Marketplace

A complete PHP + MySQL marketplace for buying and selling Mobile Legends, PUBG Mobile and Standoff2 accounts.

## Quick Start

1. Install XAMPP / WAMP / MAMP (or any PHP 8+ with MySQL).
2. Copy this folder into `htdocs/` (or your web root).
3. Open phpMyAdmin and import `database.sql` — this creates the `gamevault` database, all tables, and demo data.
4. If your MySQL user/password isn't `root` / empty, edit `includes/db.php`.
5. Visit `http://localhost/gamevault/` in your browser.

## Demo Accounts

All demo passwords are: **password123**

- Admin: `admin@gamevault.gg`
- Seller: `neon@gamevault.gg`
- Seller: `phantom@gamevault.gg`
- Seller: `glitch@gamevault.gg`

> Note: if the demo password hashes don't validate on your PHP version, just register a fresh account from `/register.php` and promote it to admin by setting `is_admin = 1` in the `users` table.

## Features

- Modern dark gaming UI with neon blue/purple glow and glassmorphism
- Home, marketplace, product, sell, profile, favorites, notifications, chat, payment, admin
- Secure PHP sessions + bcrypt password hashing
- PDO prepared statements (SQL-injection safe), escaped output everywhere
- Chart.js admin analytics
- Fully responsive

## File Structure

```
/assets/css   style.css, dashboard.css, auth.css
/assets/js    app.js, chat.js, marketplace.js
/assets/images
/includes     db.php, auth.php, header.php, footer.php, navbar.php, _card.php
/uploads      (writable, for uploaded listing images)
index.php  marketplace.php  product.php  sell.php  profile.php
favorites.php  notifications.php  chat.php  payment.php
login.php  register.php  logout.php  admin.php
database.sql
```

## Note about Supabase

Supabase uses PostgreSQL — this build uses MySQL because the spec required pure PHP/MySQL. To use Supabase you'd need to swap PDO MySQL for PDO PostgreSQL and use `pgsql:` DSN. The schema in `database.sql` is portable with minor syntax tweaks.
