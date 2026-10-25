<?php
declare(strict_types=1);

/** Private cookie-backed sessions. The raw bearer token is never stored in SQL. */
const DIETAI_SESSION_COOKIE = 'diyetai_session';
function dietai_session_cookie(string $value, int $expires): void {
    setcookie(DIETAI_SESSION_COOKIE, $value, [
        'expires' => $expires, 'path' => '/diyetisyen-ai/', 'secure' => true,
        'httponly' => true, 'samesite' => 'Lax'
    ]);
}
function dietai_session_start(PDO $pdo, int $userId): void {
    $token = bin2hex(random_bytes(32));
    $expires = time() + 7 * 86400;
    $stmt = $pdo->prepare(
        'INSERT INTO app_sessions (user_id, token_hash, expires_at) VALUES (:id, :hash, :expires)'
    );
    $stmt->execute([
        'id' => $userId,
        'hash' => hash('sha256', $token),
        'expires' => gmdate('Y-m-d H:i:s', $expires)
    ]);
    dietai_session_cookie($token, $expires);
}
function dietai_session_user(PDO $pdo): ?array {
    $token = $_COOKIE[DIETAI_SESSION_COOKIE] ?? null;
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) return null;
    $stmt = $pdo->prepare(
        "SELECT u.id, u.email, u.first_name, u.last_name FROM app_sessions s
         INNER JOIN app_users u ON u.id = s.user_id
         WHERE s.token_hash = :hash AND s.expires_at > UTC_TIMESTAMP()
           AND u.status = 'active' LIMIT 1"
    );
    $stmt->execute(['hash' => hash('sha256', $token)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($user) ? ['id' => (int)$user['id'], 'email' => $user['email'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name']] : null;
}
function dietai_session_end(PDO $pdo): void {
    $token = $_COOKIE[DIETAI_SESSION_COOKIE] ?? null;
    if (is_string($token) && preg_match('/^[a-f0-9]{64}$/D', $token)) {
        $stmt = $pdo->prepare('DELETE FROM app_sessions WHERE token_hash = :hash');
        $stmt->execute(['hash' => hash('sha256', $token)]);
    }
    dietai_session_cookie('', time() - 3600);
}
