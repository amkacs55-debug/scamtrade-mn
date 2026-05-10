<?php
// =====================================
// config.php — Supabase холболт
// =====================================

define('SUPABASE_URL', 'https://YOUR_PROJECT.supabase.co');
define('SUPABASE_ANON_KEY', 'YOUR_ANON_KEY');
define('SUPABASE_SERVICE_KEY', 'YOUR_SERVICE_ROLE_KEY'); // admin ops-д
define('OPENAI_API_KEY', 'YOUR_ANTHROPIC_API_KEY');
define('SITE_NAME', 'ML & PUBG Shop');
define('SITE_URL', 'http://localhost');

session_start();

// ---- Supabase REST API helper ----
function supabase_request(string $endpoint, string $method = 'GET', array $data = [], bool $useService = false): array {
    $key = $useService ? SUPABASE_SERVICE_KEY : SUPABASE_ANON_KEY;
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;

    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Prefer: return=representation',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($data) && in_array($method, ['POST', 'PATCH', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    return ['data' => $decoded, 'status' => $httpCode];
}

// ---- Supabase RPC / Filter helper ----
function sb_get(string $table, string $filters = ''): array {
    $endpoint = $table . ($filters ? '?' . $filters : '');
    return supabase_request($endpoint, 'GET');
}

function sb_post(string $table, array $data, bool $service = false): array {
    return supabase_request($table, 'POST', $data, $service);
}

function sb_patch(string $table, string $filters, array $data, bool $service = false): array {
    $endpoint = $table . '?' . $filters;
    return supabase_request($endpoint, 'PATCH', $data, $service);
}

// ---- Auth helpers ----
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: index.php');
        exit;
    }
}

// ---- Supabase Storage: зураг upload ----
// Bucket нэр: "account-images" (Supabase дээр public bucket үүсгэх)
function upload_image_to_supabase(array $file): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($file['type'], $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // 5MB limit

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('acc_', true) . '.' . strtolower($ext);
    $bucket   = 'account-images';
    $url      = SUPABASE_URL . '/storage/v1/object/' . $bucket . '/' . $filename;

    $fileData = file_get_contents($file['tmp_name']);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: ' . $file['type'],
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200 || $code === 201) {
        // Public URL буцаах
        return SUPABASE_URL . '/storage/v1/object/public/' . $bucket . '/' . $filename;
    }
    return false;
}

// ---- Anthropic Claude автомат хариу ----
function ai_auto_reply(string $userMessage, string $orderContext): string {
    $systemPrompt = "Та бол Mobile Legends болон PUBG Mobile account дэлгүүрийн автомат туслах. "
        . "Зөвхөн Монгол хэлээр богино, найрсаг хариу өг. "
        . "Аюулгүй байдлын үүднээс бүрэн дансны дугаар хэзээ ч бичихгүй. "
        . "Захиалга хүлээн авагдсан үед 'Таны захиалга хүлээн авлаа, удахгүй данс илгээнэ' гэж мэдэгд. "
        . "Хэрэглэгч асуулт тавьбал тусална, гэхдээ нууц мэдээлэл өгөхгүй.";

    $userPrompt = "Захиалгын мэдээлэл: {$orderContext}\n\nХэрэглэгчийн мессеж: {$userMessage}";

    $payload = [
        'model'      => 'claude-sonnet-4-5',
        'max_tokens' => 300,
        'system'     => $systemPrompt,
        'messages'   => [
            ['role' => 'user', 'content' => $userPrompt]
        ],
    ];

    $ch = curl_init('https://api.openai.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . OPENAI_API_KEY,
        'anthropic-version: 2023-06-01',
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    return $data['content'][0]['text'] ?? 'Таны захиалга хүлээн авлаа. Удахгүй холбогдоно.';
}
