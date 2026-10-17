<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_registration.php';
require_once __DIR__ . '/auth_email_verification_db.php';

/**
 * Internal registration persistence, not exposed to HTTP.
 * Creates only pending accounts. Caller is responsible for anti-abuse,
 * consent, verified delivery, and safe transaction boundaries.
 */
function auth_create_pending_account(PDO $pdo, string $email, string $password): array {
    $error = auth_validate_registration($email, $password);
    if ($error !== null) throw new InvalidArgumentException($error);
    $email = strtolower(trim($email));
    $pdo->beginTransaction();
    try {
        $insert = $pdo->prepare(
            "INSERT INTO app_users (email, password_hash, status) VALUES (:email, :hash, 'pending')"
        );
        $insert->execute(['email' => $email, 'hash' => auth_password_hash($password)]);
        $userId = (int)$pdo->lastInsertId();
        $token = auth_issue_email_verification($pdo, $userId);
        $pdo->commit();
        return ['user_id' => $userId, 'verification_token' => $token];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
