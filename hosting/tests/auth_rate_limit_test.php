<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_rate_limit.php';
function assert_auth(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$secret = str_repeat('x', 32);
$a = auth_attempt_subject(' Test@Example.com ', $secret);
$b = auth_attempt_subject('test@example.com', $secret);
assert_auth($a === $b, 'Email normalization');
assert_auth(strlen($a) === 64 && !str_contains($a, 'example'), 'Email not stored directly');
assert_auth($a !== auth_attempt_subject('other@example.com', $secret), 'Distinct accounts');
assert_auth(!auth_attempt_limit_reached(4), 'Four attempts allowed');
assert_auth(auth_attempt_limit_reached(5), 'Fifth attempt reaches limit');
assert_auth(auth_attempt_retry_seconds(200) === 700, 'Remaining cooldown');
assert_auth(auth_attempt_retry_seconds(1000) === 0, 'Expired cooldown');
echo "AUTH_RATE_LIMIT_TESTS_OK\n";
