<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_email_verification_db.php';

/**
 * Private email-verification handler. No public endpoint is enabled here.
 * Do not return the token, email, or account existence in responses.
 */
function auth_handle_email_verification(PDO $pdo, array $data): array {
    $token = $data['token'] ?? null;
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) {
        return [400, ['ok' => false, 'error' => 'invalid_verification_token']];
    }
    try {
        if (!auth_consume_email_verification($pdo, $token)) {
            return [400, ['ok' => false, 'error' => 'invalid_or_expired_token']];
        }
        return [200, ['ok' => true, 'status' => 'email_verified']];
    } catch (Throwable $exception) {
        return [503, ['ok' => false, 'error' => 'verification_unavailable']];
    }
}
