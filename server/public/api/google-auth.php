<?php
declare(strict_types=1);

/**
 * Google account application endpoint. Deploy as public_html/diyetisyen-ai/api/google-auth.php.
 * Dependencies MUST be deployed in /home2/yenilikc/diyetai_private/.
 * Fail closed: no active session is issued until separate approval/session flow is implemented.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function dietai_google_reply(int $code, string $status): never {
    http_response_code($code);
    echo json_encode(['status' => $status], JSON_UNESCAPED_UNICODE);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    dietai_google_reply(405, 'method_not_allowed');
}
if (($_SERVER['CONTENT_TYPE'] ?? '') !== 'application/json') {
    dietai_google_reply(415, 'unsupported_media_type');
}
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && $origin !== 'https://yenilikciegitim.com') {
    dietai_google_reply(403, 'forbidden');
}
$fetchSite = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
if ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'none'], true)) {
    dietai_google_reply(403, 'forbidden');
}
$raw = file_get_contents('php://input', false, null, 0, 9000);
if (!is_string($raw) || strlen($raw) > 8999) dietai_google_reply(400, 'invalid_request');
$input = json_decode($raw, true);
$credential = is_array($input) ? ($input['credential'] ?? null) : null;
$invitationCode = is_array($input) ? ($input['invitation_code'] ?? null) : null;
if ($invitationCode !== null && (!is_string($invitationCode) || strlen($invitationCode) > 64)) dietai_google_reply(400, 'invalid_request');
if (!is_string($credential) || strlen($credential) > 8192 || $credential === '') {
    dietai_google_reply(400, 'invalid_request');
}

$private = '/home2/yenilikc/diyetai_private';
try {
    require_once $private . '/auth_core.php';
    require_once $private . '/google_token_verify.php';
    require_once $private . '/google_account_service.php';
    require_once $private . '/google_session.php';
    $clientId = '255875796749-j08u7l1qmdad17qoi79e4eugtrlgsqv4.apps.googleusercontent.com';
    $identity = dietai_google_verify_id_token($credential, $clientId);
    if ($identity === null) dietai_google_reply(401, 'invalid_credential');
    $pdo = auth_db();
    $result = dietai_google_apply_verified_identity($pdo, $identity, $invitationCode);
    $status = (string) ($result['status'] ?? 'error');
    if ($status === 'active') {
        $userId = $result['user_id'] ?? null;
        if (!is_int($userId) || $userId <= 0) dietai_google_reply(503, 'temporarily_unavailable');
        dietai_session_start($pdo, $userId);
    }
    dietai_google_reply(200, $status);
} catch (Throwable $exception) {
    error_log('DiyetAI Google auth request failed: ' . get_class($exception));
    dietai_google_reply(503, 'temporarily_unavailable');
}
