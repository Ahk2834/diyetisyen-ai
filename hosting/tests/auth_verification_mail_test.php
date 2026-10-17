<?php
declare(strict_types=1);
require __DIR__ . '/../private/auth_verification_mail.php';
$token = auth_new_email_verification_token();
$mail = auth_verification_mail('test@example.com', $token);
function mail_check(bool $ok, string $label): void {
    if (!$ok) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
}
mail_check($mail['to'] === 'test@example.com', 'Recipient preserved');
mail_check(str_starts_with($mail['verification_url'], 'https://yenilikciegitim.com/diyetisyen-ai/verify-email?token='), 'HTTPS link');
mail_check(str_contains($mail['text'], $token), 'Token included in user-facing email');
mail_check(str_contains($mail['text'], '30 dakika'), 'Expiry described');
$invalid = false;
try { auth_verification_mail("bad\r\nBcc: injected@example.com", $token); }
catch (InvalidArgumentException $e) { $invalid = true; }
mail_check($invalid, 'Header injection rejected');
$invalid = false;
try { auth_verification_mail('test@example.com', 'wrong'); }
catch (InvalidArgumentException $e) { $invalid = true; }
mail_check($invalid, 'Malformed token rejected');
echo "AUTH_VERIFICATION_MAIL_TESTS_OK\n";
