<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_email_verification_db.php';
function verify_db_check(bool $ok, string $label): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
}
$pdo = new PDO('sqlite::memory:');
$rejected = false;
try { auth_issue_email_verification($pdo, 0); }
catch (InvalidArgumentException $e) { $rejected = true; }
verify_db_check($rejected, 'Invalid user rejected');
$rejected = false;
try { auth_consume_email_verification($pdo, 'invalid'); }
catch (InvalidArgumentException $e) { $rejected = true; }
verify_db_check($rejected, 'Malformed token rejected before database use');
echo "AUTH_EMAIL_VERIFICATION_DB_TESTS_OK\n";
