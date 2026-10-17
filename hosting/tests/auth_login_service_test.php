<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_login_service.php';
function check_login(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$pdo = new PDO('sqlite::memory:');
$secret = str_repeat('s', 32);
$invalidEmail = auth_login($pdo, 'not-an-email', 'sample', $secret);
check_login($invalidEmail === ['ok'=>false, 'error'=>'invalid_credentials'], 'Invalid email rejected before DB use');
$invalidPassword = auth_login($pdo, 'test@example.com', '', $secret);
check_login($invalidPassword === ['ok'=>false, 'error'=>'invalid_credentials'], 'Empty password rejected before DB use');
check_login(function_exists('auth_login'), 'Login service available');
echo "AUTH_LOGIN_SERVICE_TESTS_OK\n";
