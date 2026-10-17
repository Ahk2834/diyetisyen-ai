<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function ai_reply(int $code,array $payload): void {
    http_response_code($code);
    echo json_encode($payload,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    ai_reply(405,['error'=>'method_not_allowed']);
}
if (($_SERVER['HTTPS'] ?? '') !== 'on' && ($_SERVER['SERVER_PORT'] ?? '') !== '443') {
    ai_reply(403,['error'=>'https_required']);
}
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!is_string($origin) || !hash_equals('https://yenilikciegitim.com',$origin)) {
    ai_reply(403,['error'=>'origin_not_allowed']);
}
if (stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''),'application/json') !== 0) {
    ai_reply(415,['error'=>'json_required']);
}
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) {
    ai_reply(413,['error'=>'request_too_large']);
}
$raw = file_get_contents('php://input',false,null,0,4097);
if (!is_string($raw) || strlen($raw)>4096) {
    ai_reply(413,['error'=>'request_too_large']);
}
$data = json_decode($raw,true);
$message = is_array($data) ? ($data['message'] ?? null) : null;
if (!is_string($message) || trim($message)==='' || mb_strlen($message)>1200) {
    ai_reply(400,['error'=>'invalid_message']);
}
// No provider credentials or private health profiles are accepted by this endpoint.
// Provider integration remains disabled until rate limits and server-side
// credential management have been reviewed.
ai_reply(503,['error'=>'ai_not_configured']);
