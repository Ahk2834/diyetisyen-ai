<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_verify_request.php';
[$status, $body] = auth_handle_verify_request(['token' => str_repeat('a', 64)], ['HTTP_ORIGIN' => 'https://yenilikciegitim.com']);
if ($status !== 503 || ($body['error'] ?? '') !== 'verification_not_enabled') {
    fwrite(STDERR, "Verification must be disabled without private settings\n");
    exit(1);
}
echo "AUTH_VERIFY_REQUEST_DEFAULT_DISABLED_OK\n";
