<?php
// GameVault DB connection
$DB_HOST = 'localhost';
$DB_NAME = 'gamevault';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function current_user() { return $_SESSION['user'] ?? null; }
function require_login() {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function require_admin() {
    $u = current_user();
    if (!$u || empty($u['is_admin'])) { header('Location: index.php'); exit; }
}
