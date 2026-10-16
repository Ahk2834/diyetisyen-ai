<?php
declare(strict_types=1);

// Public, non-sensitive readiness endpoint. No database credentials or user data.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: GET');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

echo json_encode([
    'ok' => true,
    'service' => 'diyetisyen-ai-api',
    'version' => 'v1',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
