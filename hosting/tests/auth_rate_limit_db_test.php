<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_rate_limit_db.php';

function check_db_helper(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
check_db_helper(function_exists('auth_record_login_attempt'), 'Record helper exists');
check_db_helper(function_exists('auth_clear_login_attempts'), 'Clear helper exists');

$pdo = new PDO('sqlite::memory:');
$rejected = false;
try {
    auth_record_login_attempt($pdo, 'invalid');
} catch (InvalidArgumentException $e) {
    $rejected = true;
}
check_db_helper($rejected, 'Invalid subject rejected before DB operation');

$rejected = false;
try {
    auth_clear_login_attempts($pdo, 'invalid');
} catch (InvalidArgumentException $e) {
    $rejected = true;
}
check_db_helper($rejected, 'Invalid clear subject rejected');
echo "AUTH_RATE_LIMIT_DB_TESTS_OK\n";
