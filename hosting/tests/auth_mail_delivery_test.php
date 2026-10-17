<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_mail_delivery.php';
function mail_delivery_check(bool $ok, string $message): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}
$token = auth_new_email_verification_token();
$sent = [];
auth_deliver_verification_mail('test@example.com', $token,
    function (string $to, string $subject, string $body) use (&$sent): void {
        $sent[] = [$to, $subject, $body];
    }
);
mail_delivery_check(count($sent) === 1, 'One delivery');
mail_delivery_check($sent[0][0] === 'test@example.com', 'Recipient');
mail_delivery_check(str_contains($sent[0][2], $token), 'Token delivered');
$failed = false;
try {
    auth_deliver_verification_mail('test@example.com', $token,
        function (): void { throw new RuntimeException('Transport unavailable'); }
    );
} catch (RuntimeException $e) { $failed = true; }
mail_delivery_check($failed, 'Delivery errors propagate');
echo "AUTH_MAIL_DELIVERY_TESTS_OK\n";
