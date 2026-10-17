<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_login_handler.php';
function check_handler(bool $ok, string $label): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
}
[$status, $payload] = auth_handle_login([], []);
check_handler($status === 503, 'Disabled by default');
check_handler(($payload['error'] ?? '') === 'login_not_enabled', 'No private config exposes no login');
echo "AUTH_LOGIN_HANDLER_TESTS_OK\n";
