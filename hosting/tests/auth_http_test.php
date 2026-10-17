<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_http.php';

function check_http(bool $ok, string $message): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$options = auth_cookie_options();
check_http($options['secure'] === true, 'Secure required');
check_http($options['httponly'] === true, 'HttpOnly required');
check_http($options['samesite'] === 'Strict', 'SameSite Strict required');
check_http($options['path'] === '/diyetisyen-ai/api/', 'Cookie path scoped');
check_http(auth_check_same_origin(['HTTP_ORIGIN'=>'https://yenilikciegitim.com'], 'https://yenilikciegitim.com'), 'Expected origin accepted');
check_http(!auth_check_same_origin([], 'https://yenilikciegitim.com'), 'Missing origin rejected');
check_http(!auth_check_same_origin(['HTTP_ORIGIN'=>'https://evil.example'], 'https://yenilikciegitim.com'), 'Foreign origin rejected');
check_http(!auth_check_same_origin(['HTTP_ORIGIN'=>'https://yenilikciegitim.com.evil.example'], 'https://yenilikciegitim.com'), 'Lookalike origin rejected');
echo "AUTH_HTTP_TESTS_OK\n";
