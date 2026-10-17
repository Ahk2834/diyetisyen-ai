<?php
declare(strict_types=1);

/** Public API adapter, disabled by default via private server settings. */
function auth_handle_verify_request(array $data, array $server): array {
    $settingsPath = '/home2/yenilikc/diyetai_private/auth_settings.php';
    if (!is_file($settingsPath)) return [503, ['ok' => false, 'error' => 'verification_not_enabled']];
    $settings = require $settingsPath;
    if (!is_array($settings) || ($settings['auth_verification_enabled'] ?? false) !== true) {
        return [503, ['ok' => false, 'error' => 'verification_not_enabled']];
    }
    require_once __DIR__ . '/auth_http.php';
    if (!auth_check_same_origin($server, 'https://yenilikciegitim.com')) {
        return [403, ['ok' => false, 'error' => 'origin_not_allowed']];
    }
    require_once __DIR__ . '/auth_core.php';
    require_once __DIR__ . '/auth_verify_handler.php';
    try {
        return auth_handle_email_verification(auth_db(), $data);
    } catch (Throwable $exception) {
        return [503, ['ok' => false, 'error' => 'verification_unavailable']];
    }
}
