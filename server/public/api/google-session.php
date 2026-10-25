<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function dietai_session_reply(int $code, array $body): never {
    http_response_code($code);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET','POST'], true)) dietai_session_reply(405, ['ok'=>false]);
if ($method === 'POST') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $site = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
    if ($origin !== 'https://yenilikciegitim.com' ||
        ($site !== '' && $site !== 'same-origin')) dietai_session_reply(403, ['ok'=>false]);
}
try {
    require_once '/home2/yenilikc/diyetai_private/auth_core.php';
    require_once '/home2/yenilikc/diyetai_private/google_session.php';
    $pdo = auth_db();
    if ($method === 'GET') {
        $user = dietai_session_user($pdo);
        $configFile='/home2/yenilikc/diyetai_private/admin_config.php';
        $config=is_file($configFile)?require $configFile:[];
        $admins=is_array($config)&&is_array($config['admin_emails']??null)?array_map('strtolower',$config['admin_emails']):[];
        $isAdmin=$user!==null&&in_array(strtolower((string)$user['email']),$admins,true);
        dietai_session_reply(200, ['ok'=>true, 'authenticated'=>$user !== null, 'user'=>$user, 'is_admin'=>$isAdmin]);
    }
    // Only POST logout is supported. No user identifiers or permissions come from the client.
    dietai_session_end($pdo);
    dietai_session_reply(200, ['ok'=>true]);
} catch (Throwable $exception) {
    error_log('DiyetAI session request failed: ' . get_class($exception));
    dietai_session_reply(503, ['ok'=>false]);
}
