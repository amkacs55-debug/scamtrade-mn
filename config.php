<?php
// config.php - Supabase PostgreSQL Connection
session_start();

define('DB_HOST', 'aws-1-ap-northeast-1.pooler.supabase.com');
define('DB_PORT', '6543');
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.mmdvytteigecblxuvust');
define('DB_PASS', 'Hosoo0625201');   // ←←← CHANGE THIS!

$dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch(PDOException $e) {
    die("❌ Database Connection Failed: " . $e->getMessage());
}

// Business Settings
define('COMMISSION_RATE', 0.05);     // 5% commission
define('VERIFIED_PRICE', 66000);     // Monthly verified seller fee in ₮

// Create uploads folder if not exists
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}
?>
