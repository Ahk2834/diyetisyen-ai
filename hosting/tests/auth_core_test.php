<?php
declare(strict_types=1);

// Pure authentication checks; no database or private config needed.
require __DIR__ . '/../private/auth_core.php';

function check(bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$password = 'Ornek-Test-Parolasi-2026!';
$hash = auth_password_hash($password);
check($hash !== $password, 'Password must not be stored in plain text');
check(auth_verify_password($password, $hash), 'Correct password must verify');
check(!auth_verify_password('Yanlis-Parola-2026!', $hash), 'Incorrect password must fail');

$rejected = false;
try {
    auth_password_hash('short');
} catch (InvalidArgumentException $e) {
    $rejected = true;
}
check($rejected, 'Short passwords must be rejected');

$rejected = false;
try {
    auth_password_hash(str_repeat('x', 1025));
} catch (InvalidArgumentException $e) {
    $rejected = true;
}
check($rejected, 'Excessively long passwords must be rejected');

echo "AUTH_CORE_TESTS_OK\n";
