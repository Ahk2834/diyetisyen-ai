<?php
declare(strict_types=1);

/**
 * Internal email-verification token primitives.
 * The raw token must be delivered only by email and never logged.
 * Database storage uses the SHA-256 hash; token expires in 30 minutes.
 */
function auth_new_email_verification_token(): string {
    return bin2hex(random_bytes(32));
}

function auth_email_verification_hash(string $token): string {
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
        throw new InvalidArgumentException('Invalid verification token');
    }
    return hash('sha256', $token);
}

function auth_email_verification_expiry(int $issuedAt): int {
    if ($issuedAt < 0) throw new InvalidArgumentException('Invalid issue time');
    return $issuedAt + 1800;
}

function auth_email_verification_is_valid(int $expiresAt, int $now, bool $consumed): bool {
    return !$consumed && $now < $expiresAt;
}
