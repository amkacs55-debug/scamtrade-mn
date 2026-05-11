<?php

session_start();

/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

define('SUPABASE_URL', 'https://mmdvytteigecblxuvust.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1tZHZ5dHRlaWdlY2JseHV2dXN0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY1MDc4NzIsImV4cCI6MjA5MjA4Mzg3Mn0.OUdv01DC5jRNz2it4EapZ4BH3t-gtmFn4WOlFNBGC4g');
define('SUPABASE_SERVICE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im1tZHZ5dHRlaWdlY2JseHV2dXN0Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NjUwNzg3MiwiZXhwIjoyMDkyMDgzODcyfQ.hLHWIGlVv9pt-lkvbM7LYKyxw7AbLos5gCmdGnf3htM');
define('OPENAI_API_KEY', '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');

/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$host = 'aws-1-ap-northeast-1.pooler.supabase.com';
$port = '6543';
$db   = 'postgres';
$user = 'postgres.mmdvytteigecblxuvust';
$pass = 'Hosoo0625201';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| SUPABASE REQUEST HELPER
|--------------------------------------------------------------------------
*/

function supabase_request(
    string $endpoint,
    string $method = 'GET',
    array $data = [],
    bool $useService = false
): array {

    $key = $useService
        ? SUPABASE_SERVICE_KEY
        : SUPABASE_ANON_KEY;

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

    // PRODUCTION дээр TRUE болго
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    if (!empty($data) && in_array($method, ['POST', 'PATCH', 'PUT'])) {
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );
    }

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $curlErr = curl_error($ch);

    curl_close($ch);

    if ($curlErr) {
        return [
            'status' => 0,
            'data'   => [
                'message' => $curlErr
            ]
        ];
    }

    $decoded = json_decode($response, true);

    return [
        'status' => $httpCode,
        'data'   => $decoded
    ];
}

/*
|--------------------------------------------------------------------------
| SIMPLE HELPERS
|--------------------------------------------------------------------------
*/

function sb_get(string $table, string $filters = ''): array {

    $endpoint = $table;

    if ($filters) {
        $endpoint .= '?' . $filters;
    }

    return supabase_request($endpoint, 'GET');
}

function sb_post(
    string $table,
    array $data,
    bool $service = false
): array {

    return supabase_request(
        $table,
        'POST',
        $data,
        $service
    );
}

function sb_patch(
    string $table,
    string $filters,
    array $data,
    bool $service = false
): array {

    $endpoint = $table . '?' . $filters;

    return supabase_request(
        $endpoint,
        'PATCH',
        $data,
        $service
    );
}

/*
|--------------------------------------------------------------------------
| AUTH HELPERS
|--------------------------------------------------------------------------
*/

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['role'])
        && $_SESSION['role'] === 'admin';
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

/*
|--------------------------------------------------------------------------
| IMAGE UPLOAD TO SUPABASE STORAGE
|--------------------------------------------------------------------------
*/

function upload_image_to_supabase(array $file): string|false {

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $allowed = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif'
    ];

    if (!in_array($file['type'], $allowed)) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $ext = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    $filename = uniqid('acc_', true) . '.' . $ext;

    $bucket = 'account-images';

    $uploadUrl =
        SUPABASE_URL .
        '/storage/v1/object/' .
        $bucket .
        '/' .
        $filename;

    $fileData = file_get_contents($file['tmp_name']);

    $ch = curl_init($uploadUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: ' . $file['type'],
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {

        return
            SUPABASE_URL .
            '/storage/v1/object/public/' .
            $bucket .
            '/' .
            $filename;
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| OPENAI AUTO REPLY
|--------------------------------------------------------------------------
*/

function ai_auto_reply(
    string $userMessage,
    string $orderContext
): string {

    $payload = [
        'model' => 'gpt-4o-mini',

        'messages' => [

            [
                'role' => 'system',

                'content' =>
                    'Та бол gaming account shop assistant. '
                    . 'Монгол хэлээр богино хариул.'
            ],

            [
                'role' => 'user',

                'content' =>
                    "Захиалга: {$orderContext}\n\n"
                    . "Мессеж: {$userMessage}"
            ]
        ],

        'max_tokens' => 300
    ];

    $ch = curl_init(
        'https://api.openai.com/v1/chat/completions'
    );

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        json_encode($payload)
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
    ]);

    $response = curl_exec($ch);

    curl_close($ch);

    $data = json_decode($response, true);

    return $data['choices'][0]['message']['content']
        ?? 'Таны захиалга хүлээн авлаа.';
}
