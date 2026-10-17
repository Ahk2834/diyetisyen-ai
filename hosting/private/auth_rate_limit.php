<?php
declare(strict_types=1);

/**
 * Login attempt limiting primitives.
 * Requires a database transaction with a subject-level lock for atomicity.
 * Subject is an HMAC of normalized email, not the raw email.
 */
function auth_attempt_subject(string $email, string $secret): string {
    if (strlen($secret) < 32) throw new InvalidArgumentException('Secret too short');
    $normalized = strtolower(trim($email));
    if ($normalized === '' || strlen($normalized) > 254) throw new InvalidArgumentException('Invalid email');
    return hash_hmac('sha256', $normalized, $secret);
}

function auth_attempt_limit_reached(int $recentAttempts, int $maxAttempts = 5): bool {
    if ($recentAttempts < 0 || $maxAttempts < 1) throw new InvalidArgumentException('Invalid limits');
    return $recentAttempts >= $maxAttempts;
}

function auth_attempt_retry_seconds(int $oldestAgeSeconds, int $windowSeconds = 900): int {
    if ($oldestAgeSeconds < 0 || $windowSeconds < 1) throw new InvalidArgumentException('Invalid window');
    return max(0, $windowSeconds - $oldestAgeSeconds);
}
