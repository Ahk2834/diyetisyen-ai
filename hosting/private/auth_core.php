<?php
declare(strict_types=1);

/**
 * Internal authentication primitives. Not a public API endpoint.
 * Requires PHP 7.4+, PDO MySQL and HTTPS at the reverse proxy.
 */
function auth_db(): PDO {
    $cfg = require '/home2/yenilikc/diyetai_private/db_config.php';
    if (!is_array($cfg)) throw new RuntimeException('Invalid configuration');
    foreach (['host','port','database','username','password','charset'] as $key) {
        if (!isset($cfg[$key]) || !is_scalar($cfg[$key])) throw new RuntimeException('Invalid configuration');
    }
    $host=(string)$cfg['host'];
    $db=(string)$cfg['database'];
    $charset=(string)$cfg['charset'];
    $port=(int)$cfg['port'];
    if (preg_match('/[;=\r\n]/', $host.$db) || !preg_match('/^[a-zA-Z0-9_]+$/',$charset) || $port<1 || $port>65535) {
        throw new RuntimeException('Invalid configuration');
    }
    return new PDO("mysql:host={$host};port={$port};dbname={$db};charset={$charset}",
        (string)$cfg['username'],(string)$cfg['password'],[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES=>false,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        ]);
}
function auth_password_hash(string $password): string {
    if (strlen($password)<12 || strlen($password)>1024) throw new InvalidArgumentException('Invalid password length');
    return password_hash($password, PASSWORD_DEFAULT);
}
function auth_verify_password(string $password, string $hash): bool {
    return password_verify($password,$hash);
}
function auth_issue_session(PDO $pdo, int $userId): string {
    $token=bin2hex(random_bytes(32));
    $hash=hash('sha256',$token);
    $stmt=$pdo->prepare('INSERT INTO app_sessions (user_id,token_hash,expires_at) VALUES (:uid,:hash,DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 DAY))');
    $stmt->execute(['uid'=>$userId,'hash'=>$hash]);
    return $token;
}
function auth_find_session(PDO $pdo,string $token): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/D',$token)) return null;
    $stmt=$pdo->prepare('SELECT u.id,u.email FROM app_sessions s JOIN app_users u ON u.id=s.user_id WHERE s.token_hash=:hash AND s.expires_at>UTC_TIMESTAMP() AND u.status=\'active\' LIMIT 1');
    $stmt->execute(['hash'=>hash('sha256',$token)]);
    $row=$stmt->fetch();
    return $row ?: null;
}
function auth_revoke_session(PDO $pdo,string $token): void {
    if (!preg_match('/^[a-f0-9]{64}$/D',$token)) return;
    $stmt=$pdo->prepare('DELETE FROM app_sessions WHERE token_hash=:hash');
    $stmt->execute(['hash'=>hash('sha256',$token)]);
}
