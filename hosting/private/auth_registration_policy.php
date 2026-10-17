<?php
declare(strict_types=1);

/**
 * Generic registration eligibility policy.
 * The public registration endpoint stays disabled until delivery,
 * consent, rate limiting and abuse prevention are configured.
 */
function auth_registration_policy(array $data): array {
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $consent = $data['privacy_consent'] ?? null;
    if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        return ['ok' => false, 'error' => 'invalid_email'];
    }
    if (!is_string($password) || strlen($password) < 12 || strlen($password) > 1024) {
        return ['ok' => false, 'error' => 'invalid_password'];
    }
    if ($consent !== true) {
        return ['ok' => false, 'error' => 'privacy_consent_required'];
    }
    return ['ok' => true];
}
