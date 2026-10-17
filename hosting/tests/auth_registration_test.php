<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_registration.php';
function assert_registration(bool $value, string $message): void {
    if (!$value) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
assert_registration(auth_validate_registration('person@example.com', 'Strong-Password-2026!') === null, 'Valid registration input');
assert_registration(auth_validate_registration('not-an-email', 'Strong-Password-2026!') === 'invalid_email', 'Invalid email rejected');
assert_registration(auth_validate_registration('person@example.com', 'short') === 'invalid_password', 'Short password rejected');
assert_registration(auth_validate_registration('person@example.com', str_repeat('x', 1025)) === 'invalid_password', 'Long password rejected');
$pdo = new PDO('sqlite::memory:');
$blocked = false;
try { auth_register_unverified($pdo, 'person@example.com', 'Strong-Password-2026!'); }
catch (LogicException $exception) { $blocked = $exception->getMessage() === 'registration_not_enabled'; }
assert_registration($blocked, 'Registration remains disabled');
echo "AUTH_REGISTRATION_TESTS_OK\n";
