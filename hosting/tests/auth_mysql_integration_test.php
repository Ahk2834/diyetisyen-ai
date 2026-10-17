<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_email_verification_db.php';
$dsn = getenv('AUTH_TEST_MYSQL_DSN');
if (!$dsn) { fwrite(STDERR, "Missing test-only MySQL DSN\n"); exit(1); }
$pdo = new PDO($dsn, getenv('AUTH_TEST_MYSQL_USER') ?: '', getenv('AUTH_TEST_MYSQL_PASSWORD') ?: '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$root = dirname(__DIR__);
foreach (['001_auth_schema.sql','003_email_verification.sql','004_pending_account_status.sql'] as $migration) {
    $sql = file_get_contents($root . '/sql/' . $migration);
    // Remove full-line SQL comments before splitting into statements.
    $sql = preg_replace('/^[ \t]*--[^\r\n]*(?:\r?\n|$)/m', '', $sql);
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') $pdo->exec($statement);
    }
}
$address = 'ci-' . bin2hex(random_bytes(8)) . '@example.invalid';
$stmt = $pdo->prepare('INSERT INTO app_users (email, password_hash) VALUES (?, ?)');
$stmt->execute([$address, password_hash('IntegrationTestPassword2026!', PASSWORD_DEFAULT)]);
$userId = (int)$pdo->lastInsertId();
$check = $pdo->prepare('SELECT status FROM app_users WHERE id = ?');
$check->execute([$userId]);
if ($check->fetchColumn() !== 'pending') throw new RuntimeException('New account must be pending');
$token = auth_issue_email_verification($pdo, $userId);
if (!auth_consume_email_verification($pdo, $token)) throw new RuntimeException('First verification failed');
if (auth_consume_email_verification($pdo, $token)) throw new RuntimeException('Replay was accepted');
$check->execute([$userId]);
if ($check->fetchColumn() !== 'active') throw new RuntimeException('Account was not activated');
$count = $pdo->prepare('SELECT COUNT(*) FROM app_email_verifications WHERE user_id = ? AND consumed_at IS NOT NULL');
$count->execute([$userId]);
if ((int)$count->fetchColumn() !== 1) throw new RuntimeException('Token was not consumed');
$pdo->prepare('DELETE FROM app_users WHERE id = ?')->execute([$userId]);
echo "AUTH_MYSQL_INTEGRATION_OK\n";
