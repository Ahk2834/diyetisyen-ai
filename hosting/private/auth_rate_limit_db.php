<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_rate_limit.php';

/**
 * Transactionally check and record an attempted login.
 * Uses a per-subject MySQL named lock to serialize concurrent attempts.
 * Returns false when the limit has been reached; caller must reject login.
 * Do not use until 002_login_rate_limit.sql is applied and HMAC secret is configured.
 */
function auth_record_login_attempt(PDO $pdo, string $subjectHash, int $maxAttempts = 5): bool {
    if (!preg_match('/^[a-f0-9]{64}$/D', $subjectHash) || $maxAttempts < 1) {
        throw new InvalidArgumentException('Invalid login attempt arguments');
    }
    $lockName = 'dai_login_' . substr($subjectHash, 0, 40);
    $lock = $pdo->prepare('SELECT GET_LOCK(:lockname, 5)');
    $lock->execute(['lockname' => $lockName]);
    if ((int)$lock->fetchColumn() !== 1) {
        throw new RuntimeException('Login lock unavailable');
    }
    try {
        $count = $pdo->prepare('SELECT COUNT(*) FROM app_login_attempts WHERE subject_hash = :subject AND attempted_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)');
        $count->execute(['subject' => $subjectHash]);
        if (auth_attempt_limit_reached((int)$count->fetchColumn(), $maxAttempts)) {
            return false;
        }
        $insert = $pdo->prepare('INSERT INTO app_login_attempts (subject_hash, attempted_at) VALUES (:subject, UTC_TIMESTAMP())');
        $insert->execute(['subject' => $subjectHash]);
        return true;
    } finally {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(:lockname)');
        $release->execute(['lockname' => $lockName]);
    }
}

/** Clear a subject's attempts after successful authentication. */
function auth_clear_login_attempts(PDO $pdo, string $subjectHash): void {
    if (!preg_match('/^[a-f0-9]{64}$/D', $subjectHash)) throw new InvalidArgumentException('Invalid subject');
    $stmt = $pdo->prepare('DELETE FROM app_login_attempts WHERE subject_hash = :subject');
    $stmt->execute(['subject' => $subjectHash]);
}
