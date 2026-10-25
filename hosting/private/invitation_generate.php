<?php
declare(strict_types=1);
/** CLI only, keep outside public_html. Run: php invitation_generate.php */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/auth_core.php';
$pdo=auth_db();
$code=bin2hex(random_bytes(16));
$hash=hash('sha256',$code);
$stmt=$pdo->prepare('INSERT INTO app_invitation_codes (code_hash,expires_at) VALUES (:hash,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 DAY))');
$stmt->execute(['hash'=>$hash]);
fwrite(STDOUT,"Single-use invitation code (show once): ".$code.PHP_EOL);
