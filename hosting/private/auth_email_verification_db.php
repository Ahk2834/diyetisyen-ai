<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_email_verification.php';

/**
 * Issue a verification token for a pending account.
 * Must be called by a reviewed registration flow; email delivery is separate.
 */
function auth_issue_email_verification(PDO $pdo, int $userId): string {
    if ($userId < 1) throw new InvalidArgumentException('Invalid user');
    $token = auth_new_email_verification_token();
    $hash = auth_email_verification_hash($token);
    $stmt = $pdo->prepare(
        'INSERT INTO app_email_verifications (user_id, token_hash, expires_at) ' .
        'VALUES (:uid, :hash, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 MINUTE))'
    );
    $stmt->execute(['uid' => $userId, 'hash' => $hash]);
    return $token;
}

/**
 * Consume token atomically, activate its pending user, and invalidate all
 * other tokens for that user. Returns false for invalid/used/expired tokens.
 */
function auth_consume_email_verification(PDO $pdo, string $token): bool {
    $hash = auth_email_verification_hash($token);
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'SELECT id, user_id FROM app_email_verifications ' .
            'WHERE token_hash = :hash AND consumed_at IS NULL ' .
            'AND expires_at > UTC_TIMESTAMP() FOR UPDATE'
        );
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->rollBack();
            return false;
        }
        $update = $pdo->prepare(
            "UPDATE app_users SET status = 'active' WHERE id = :uid AND status = 'pending'"
        );
        $update->execute(['uid' => (int)$row['user_id']]);
        if ($update->rowCount() !== 1) {
            $pdo->rollBack();
            return false;
        }
        $consume = $pdo->prepare(
            'UPDATE app_email_verifications SET consumed_at = UTC_TIMESTAMP() ' .
            'WHERE user_id = :uid AND consumed_at IS NULL'
        );
        $consume->execute(['uid' => (int)$row['user_id']]);
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
