<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_verification_mail.php';

/**
 * Delivery adapter for server-managed SMTP implementations.
 * A transport callback receives recipient, subject and text; it must
 * throw on delivery failure. Do not log tokens or email bodies.
 * This adapter never sends email on its own.
 */
function auth_deliver_verification_mail(
    string $email,
    string $token,
    callable $transport
): void {
    $mail = auth_verification_mail($email, $token);
    $transport($mail['to'], $mail['subject'], $mail['text']);
}
