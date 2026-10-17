<?php
declare(strict_types=1);

/**
 * Internal API login handler. Not a public entrypoint.
 * Requires the caller to validate method, HTTPS, JSON and request size.
 * Login is disabled unless private settings explicitly enable it.
 */
function auth_handle_login(array $data, array $server): array {
    $configPath = '/home2/yenilikc/diyetai_private/auth_settings.php';
    if (!is_file($configPath)) return [503, ['ok'=>false,'error'=>'login_not_enabled']];
    $config = require $configPath;
    if (!is_array($config) || ($config['auth_login_enabled'] ?? false) !== true) {
        return [503, ['ok'=>false,'error'=>'login_not_enabled']];
    }
    $secret = $config['rate_limit_secret'] ?? null;
    if (!is_string($secret) || strlen($secret) < 32) {
        return [503, ['ok'=>false,'error'=>'login_not_enabled']];
    }
    require_once __DIR__ . '/auth_http.php';
    if (!auth_check_same_origin($server, 'https://yenilikciegitim.com')) {
        return [403, ['ok'=>false,'error'=>'origin_not_allowed']];
    }
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    if (!is_string($email) || !is_string($password) ||
        strlen($email) > 254 || strlen($password) > 1024) {
        return [400, ['ok'=>false,'error'=>'invalid_input']];
    }
    require_once __DIR__ . '/auth_login_service.php';
    try {
        $result = auth_login(auth_db(), $email, $password, $secret);
        if (!$result['ok']) {
            $status = $result['error'] === 'too_many_attempts' ? 429 : 401;
            return [$status, ['ok'=>false,'error'=>$result['error']]];
        }
        auth_set_session_cookie($result['token']);
        return [200, ['ok'=>true,'user'=>$result['user']]];
    } catch (Throwable $exception) {
        error_log('Diyetisyen AI login unavailable');
        return [503, ['ok'=>false,'error'=>'service_unavailable']];
    }
}
