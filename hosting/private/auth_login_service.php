<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_core.php';
require_once __DIR__ . '/auth_rate_limit_db.php';

/**
 * Private login service. Do not expose directly over HTTP.
 * Caller must first validate HTTPS, same-origin request, and input lengths.
 * The secret must be server-side random bytes (>=32 bytes), never in Git.
 * The caller must set the session as an HttpOnly cookie, never return token in JSON.
 */
function auth_login(PDO $pdo, string $email, string $password, string $rateLimitSecret): array {
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 ||
        strlen($password) < 1 || strlen($password) > 1024) {
        return ['ok' => false, 'error' => 'invalid_credentials'];
    }

    $subject = auth_attempt_subject($email, $rateLimitSecret);
    if (!auth_record_login_attempt($pdo, $subject)) {
        return ['ok' => false, 'error' => 'too_many_attempts'];
    }

    $stmt = $pdo->prepare("SELECT id, email, password_hash FROM app_users WHERE email = :email AND status = 'active' LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // A constant dummy hash makes missing accounts less distinguishable by timing.
    $dummyHash = '$2y$10$C6UzMDM.H6dfI/f/IKcEeO5g7gV4c7oWl0OP4S3vT4YzG7tJQEdZe';
    $valid = password_verify($password, $user ? (string)$user['password_hash'] : $dummyHash);
    if (!$user || !$valid) {
        return ['ok' => false, 'error' => 'invalid_credentials'];
    }

    $token = auth_issue_session($pdo, (int)$user['id']);
    auth_clear_login_attempts($pdo, $subject);
    return ['ok' => true, 'token' => $token, 'user' => ['id' => (int)$user['id'], 'email' => (string)$user['email']]];
}
