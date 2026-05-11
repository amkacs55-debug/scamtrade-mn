<?php
// =====================================
// config.php — Supabase холболт
// =====================================

define('SUPABASE_URL', 'https://mmdvytteigecblxuvust.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1tZHZ5dHRlaWdlY2JseHV2dXN0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY1MDc4NzIsImV4cCI6MjA5MjA4Mzg3Mn0.OUdv01DC5jRNz2it4EapZ4BH3t-gtmFn4WOlFNBGC4g');
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1tZHZ5dHRlaWdlY2JseHV2dXN0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NjUwNzg3MiwiZXhwIjoyMDkyMDgzODcyfQ.hLHWIGlVv9pt-lkvbM7LYKyxw7AbLos5gCmdGnf3htM'); // admin ops-д
define('OPENAI_API_KEY', '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');
define('SITE_NAME', 'ML & PUBG Shop');
define('SITE_URL', 'https://scamtrade-mn.onrender.com');

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
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // зарим серверт SSL асуудал гардаг

    if (!empty($data) && in_array($method, ['POST', 'PATCH', 'PUT'])) {
        // images array-г зөв JSON болгох
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['data' => ['message' => 'cURL алдаа: ' . $curlErr], 'status' => 0];
    }

    $decoded = json_decode($response, true);

    // Supabase заримдаа array буцаана, заримдаа object
    if (!is_array($decoded)) {
        $decoded = ['raw' => $response];
    }

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

// ---- OpenAI автомат хариу ----
function ai_auto_reply(string $userMessage, string $orderContext): string {
    $payload = [
        'model'      => 'gpt-4o-mini',
        'max_tokens' => 300,
        'messages'   => [
            [
                'role'    => 'system',
                'content' => 'Та бол Mobile Legends болон PUBG Mobile account дэлгүүрийн автомат туслах. Зөвхөн Монгол хэлээр богино, найрсаг хариу өг. Аюулгүй байдлын үүднээс бүрэн дансны дугаар хэзээ ч бичихгүй. Захиалга хүлээн авагдсан үед "Таны захиалга хүлээн авлаа, удахгүй данс илгээнэ" гэж мэдэгд.',
            ],
            [
                'role'    => 'user',
                'content' => "Захиалгын мэдээлэл: {$orderContext}\n\nХэрэглэгчийн мессеж: {$userMessage}",
            ],
        ],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    return $data['choices'][0]['message']['content'] ?? 'Таны захиалга хүлээн авлаа. Удахгүй холбогдоно.';
}
