<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_registration_policy.php';
function registration_check(bool $ok, string $label): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
}
$valid = ['email' => 'user@example.com', 'password' => 'ExamplePassword2026!', 'privacy_consent' => true];
registration_check(auth_registration_policy($valid)['ok'] === true, 'Valid registration accepted');
foreach ([
    array_merge($valid, ['privacy_consent' => false]),
    array_merge($valid, ['privacy_consent' => 'true']),
    array_merge($valid, ['privacy_consent' => null])
] as $case) {
    registration_check(auth_registration_policy($case)['error'] === 'privacy_consent_required', 'Explicit consent required');
}
registration_check(auth_registration_policy(array_merge($valid, ['email' => 'invalid']))['error'] === 'invalid_email', 'Invalid email rejected');
registration_check(auth_registration_policy(array_merge($valid, ['password' => 'short']))['error'] === 'invalid_password', 'Weak password rejected');
echo "AUTH_REGISTRATION_POLICY_OK\n";
