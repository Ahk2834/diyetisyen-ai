<?php
declare(strict_types=1);

/**
 * Static schema contract check: prevents accidental mismatch between
 * authentication SQL migrations and PHP queries. No database credentials needed.
 */
$root = dirname(__DIR__);
$schema = file_get_contents($root . '/sql/001_auth_schema.sql');
$pending = file_get_contents($root . '/sql/004_pending_account_status.sql');
$verification = file_get_contents($root . '/sql/003_email_verification.sql');
$service = file_get_contents($root . '/private/auth_login_service.php');
$verificationDb = file_get_contents($root . '/private/auth_email_verification_db.php');
$checks = [
    'user table' => str_contains($schema, 'CREATE TABLE IF NOT EXISTS app_users'),
    'email unique' => str_contains($schema, 'UNIQUE KEY uq_app_users_email'),
    'session table' => str_contains($schema, 'CREATE TABLE IF NOT EXISTS app_sessions'),
    'pending status migration' => str_contains($pending, "ENUM('pending','active','disabled')"),
    'pending default' => str_contains($pending, "DEFAULT 'pending'"),
    'verification table' => str_contains($verification, 'CREATE TABLE IF NOT EXISTS app_email_verifications'),
    'unique verification hash' => str_contains($verification, 'UNIQUE KEY uq_email_verification_token'),
    'consumed field' => str_contains($verification, 'consumed_at DATETIME NULL'),
    'login active-only' => str_contains($service, "'active'"),
    'verification pending-only' => str_contains($verificationDb, "status = 'pending'"),
    'verification activates' => str_contains($verificationDb, "status = 'active'"),
    'verification transactional' => str_contains($verificationDb, 'beginTransaction()'),
];
foreach ($checks as $label => $ok) {
    if (!$ok) {
        fwrite(STDERR, "SCHEMA CONTRACT FAIL: {$label}\n");
        exit(1);
    }
}
echo "AUTH_SCHEMA_CONTRACT_OK\n";
