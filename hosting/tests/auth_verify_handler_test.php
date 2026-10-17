<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_verify_handler.php';
function verify_assert(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$pdo = new PDO('sqlite::memory:');
foreach ([[], ['token' => 12], ['token' => 'wrong'], ['token' => str_repeat('z', 64)]] as $input) {
    [$status, $payload] = auth_handle_email_verification($pdo, $input);
    verify_assert($status === 400 && $payload['error'] === 'invalid_verification_token', 'Malformed token rejected');
}
echo "AUTH_VERIFY_HANDLER_TESTS_OK\n";
