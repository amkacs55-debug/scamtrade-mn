<?php

// GameVault Supabase DB connection

$DB_HOST = 'aws-1-ap-northeast-1.pooler.supabase.com';
$DB_PORT = '6543';
$DB_NAME = 'postgres';
$DB_USER = 'postgres.mmdvytteigecblxuvust';
$DB_PASS = 'Hosoo0625201';

try {

    $pdo = new PDO(
        "pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;sslmode=require",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));

}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Escape helper
function e($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
