<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_email_verification.php';
function verify_check(bool $ok, string $message): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$first = auth_new_email_verification_token();
$second = auth_new_email_verification_token();
verify_check(strlen($first) === 64 && ctype_xdigit($first), 'Random token format');
verify_check($first !== $second, 'Random tokens must differ');
verify_check(strlen(auth_email_verification_hash($first)) === 64, 'Token hashed');
verify_check(auth_email_verification_hash($first) !== $first, 'Hash differs from token');
verify_check(auth_email_verification_expiry(1000) === 2800, 'Thirty-minute expiration');
verify_check(auth_email_verification_is_valid(2800, 2799, false), 'Unexpired token valid');
verify_check(!auth_email_verification_is_valid(2800, 2800, false), 'Expired token rejected');
verify_check(!auth_email_verification_is_valid(2800, 1001, true), 'Used token rejected');
$invalid = false;
try { auth_email_verification_hash('invalid'); }
catch (InvalidArgumentException $e) { $invalid = true; }
verify_check($invalid, 'Malformed token rejected');
echo "AUTH_EMAIL_VERIFICATION_TESTS_OK\n";
