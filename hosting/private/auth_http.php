<?php
declare(strict_types=1);

/**
 * Cookie policy for same-origin, HTTPS-only authentication.
 * Call before any response body is sent.
 * Tokens must be generated server-side and never exposed to JavaScript.
 */
function auth_cookie_options(int $expires = 0): array {
    return [
        'expires' => $expires,
        'path' => '/diyetisyen-ai/api/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ];
}

function auth_set_session_cookie(string $token): void {
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
        throw new InvalidArgumentException('Invalid session token');
    }
    if (!setcookie('diyetai_session', $token, auth_cookie_options())) {
        throw new RuntimeException('Unable to set session cookie');
    }
}

function auth_clear_session_cookie(): void {
    if (!setcookie('diyetai_session', '', auth_cookie_options(time() - 3600))) {
        throw new RuntimeException('Unable to clear session cookie');
    }
}

/** CSRF guard for state-changing requests; fail closed when Origin is absent. */
function auth_check_same_origin(array $server, string $expectedOrigin): bool {
    $origin = $server['HTTP_ORIGIN'] ?? null;
    if (!is_string($origin) || $origin === '') return false;
    return hash_equals($expectedOrigin, $origin);
}
