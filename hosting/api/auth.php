<?php
declare(strict_types=1);

/** Public authentication endpoint. Requires HTTPS and the private auth_core.php. */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, ['ok'=>false,'error'=>'method_not_allowed']);
}
if (($_SERVER['HTTPS'] ?? '') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') !== '443') {
    respond(403, ['ok'=>false,'error'=>'https_required']);
}
if (stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''),'application/json') !== 0) {
    respond(415, ['ok'=>false,'error'=>'json_required']);
}
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 8192) {
    respond(413, ['ok'=>false,'error'=>'request_too_large']);
}
$raw=file_get_contents('php://input',false,null,0,8193);
if ($raw === false || strlen($raw)>8192) respond(413,['ok'=>false,'error'=>'request_too_large']);
$data=json_decode($raw,true);
if (!is_array($data)) respond(400,['ok'=>false,'error'=>'invalid_json']);

$action=$data['action'] ?? null;
if (!is_string($action)) respond(400,['ok'=>false,'error'=>'invalid_action']);

// Account registration remains deliberately disabled until rate limiting,
// CSRF/origin policy, consent and account recovery are implemented.
if ($action==='register') respond(503,['ok'=>false,'error'=>'registration_not_enabled']);
if ($action!=='login') respond(400,['ok'=>false,'error'=>'unsupported_action']);

$email=$data['email'] ?? null;
$password=$data['password'] ?? null;
if (!is_string($email) || !is_string($password) || strlen($email)>254 || strlen($password)>1024) {
    respond(400,['ok'=>false,'error'=>'invalid_input']);
}
// Login also disabled until abuse protection and cookie-based sessions are ready.
respond(503,['ok'=>false,'error'=>'login_not_enabled']);
