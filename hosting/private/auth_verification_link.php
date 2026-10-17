<?php
declare(strict_types=1);

/**
 * Verification link renderer for future frontend integration.
 * Only same-site HTTPS URLs are accepted; token never enters logs here.
 */
function auth_verification_link(string $token): string {
    if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
        throw new InvalidArgumentException('Invalid verification token');
    }
    return 'https://yenilikciegitim.com/diyetisyen-ai/verify-email?token=' . rawurlencode($token);
}
