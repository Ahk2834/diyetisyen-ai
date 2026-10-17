<?php
declare(strict_types=1);
require_once __DIR__ . '/auth_core.php';

/**
 * Registration validation and persistence for internal use only.
 * Public registration must remain disabled until email verification,
 * abuse controls and privacy/consent requirements are implemented.
 */
function auth_validate_registration(string $email, string $password): ?string {
    $email = strtolower(trim($email));
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'invalid_email';
    }
    if (strlen($password) < 12 || strlen($password) > 1024) {
        return 'invalid_password';
    }
    return null;
}

function auth_register_unverified(PDO $pdo, string $email, string $password): int {
    $error = auth_validate_registration($email, $password);
    if ($error !== null) throw new InvalidArgumentException($error);
    $email = strtolower(trim($email));
    // Registration stays disabled pending verified-email workflow.
    // Do not persist accounts as active without ownership verification.
    throw new LogicException('registration_not_enabled');
}
