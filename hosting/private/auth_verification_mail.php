<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_email_verification.php';
require_once __DIR__ . '/auth_verification_link.php';

/**
 * Construct verification mail content. Delivery is deliberately separate.
 * Never log the generated URL or raw token.
 */
function auth_verification_mail(string $email, string $token): array {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        throw new InvalidArgumentException('Invalid email');
    }
    auth_email_verification_hash($token);
    $url = auth_verification_link($token);
    return [
        'to' => $email,
        'subject' => 'Diyetisyen AI - E-posta doğrulama',
        'text' => "Diyetisyen AI hesabınız için e-posta adresinizi doğrulayın:\n\n" .
            $url . "\n\nBu bağlantı 30 dakika geçerlidir ve yalnızca bir kez kullanılabilir. " .
            "Bu hesabı siz oluşturmadıysanız mesajı dikkate almayın.\n",
        'verification_url' => $url,
    ];
}
