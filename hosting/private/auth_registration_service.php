<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_pending_registration.php';
require_once __DIR__ . '/auth_mail_delivery.php';

/**
 * Internal registration orchestration. No public route is enabled.
 * Failure to send mail leaves a pending account, never an active account.
 * Do not call from HTTP until consent, anti-abuse and duplicate-account
 * handling have been reviewed.
 */
function auth_register_and_send(
    PDO $pdo,
    string $email,
    string $password,
    callable $transport
): array {
    $created = auth_create_pending_account($pdo, $email, $password);
    try {
        auth_deliver_verification_mail(strtolower(trim($email)), $created['verification_token'], $transport);
    } catch (Throwable $exception) {
        // No raw verification token or email content should reach logs.
        return ['ok' => false, 'error' => 'verification_delivery_failed'];
    }
    return ['ok' => true, 'status' => 'verification_required'];
}
