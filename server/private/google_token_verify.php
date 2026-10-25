<?php
declare(strict_types=1);

/**
 * Verify Google Identity Services ID tokens without third-party paid services.
 * This file belongs OUTSIDE public_html; never use decoded JWT claims without verification.
 */
function dietai_google_b64url(string $value): string|false {
    if (!preg_match('/^[A-Za-z0-9_-]+$/D', $value)) return false;
    return base64_decode(strtr($value, '-_', '+/'), true);
}
function dietai_google_verify_id_token(string $jwt, string $clientId): ?array {
    if (strlen($jwt) > 8192 || substr_count($jwt, '.') !== 2) return null;
    $parts = explode('.', $jwt);
    $headerJson = dietai_google_b64url($parts[0]);
    $payloadJson = dietai_google_b64url($parts[1]);
    $signature = dietai_google_b64url($parts[2]);
    if ($headerJson === false || $payloadJson === false || $signature === false) return null;
    $header = json_decode($headerJson, true);
    $claims = json_decode($payloadJson, true);
    if (!is_array($header) || !is_array($claims) || ($header['alg'] ?? null) !== 'RS256') return null;
    $kid = $header['kid'] ?? null;
    if (!is_string($kid) || strlen($kid) > 256 || $kid === '') return null;

    $now = time();
    $iss = $claims['iss'] ?? null;
    if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) return null;
    if (!is_string($claims['aud'] ?? null) || !hash_equals($clientId, $claims['aud'])) return null;
    if (!is_int($claims['exp'] ?? null) || $claims['exp'] <= $now || $claims['exp'] > $now + 86400) return null;
    if (!is_int($claims['iat'] ?? null) || $claims['iat'] > $now + 300 || $claims['iat'] < $now - 86400) return null;
    if (($claims['email_verified'] ?? false) !== true) return null;
    if (!is_string($claims['sub'] ?? null) || !preg_match('/^[0-9]{1,255}$/D', $claims['sub'])) return null;
    if (!is_string($claims['email'] ?? null) || !filter_var($claims['email'], FILTER_VALIDATE_EMAIL) || strlen($claims['email']) > 254) return null;

    // Google's public certificate endpoint is HTTPS and must be fetched with TLS verification.
    // Never accept a client-supplied URL, key, or certificate.
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/certs');
    if ($ch === false) return null;
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS => 0,
    ]);
    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!is_string($body) || strlen($body) > 65536 || $httpCode !== 200) return null;
    $jwks = json_decode($body, true);
    if (!is_array($jwks) || !is_array($jwks['keys'] ?? null)) return null;

    foreach ($jwks['keys'] as $key) {
        if (!is_array($key) || ($key['kid'] ?? null) !== $kid ||
            ($key['kty'] ?? null) !== 'RSA' || ($key['alg'] ?? null) !== 'RS256' ||
            ($key['use'] ?? null) !== 'sig') continue;
        if (!is_array($key['x5c'] ?? null) || !is_string($key['x5c'][0] ?? null)) continue;
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split($key['x5c'][0], 64, "\n") . "-----END CERTIFICATE-----\n";
        $publicKey = openssl_pkey_get_public($pem);
        if ($publicKey === false) continue;
        $verified = openssl_verify($parts[0] . '.' . $parts[1], $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verified === 1) return ['sub' => $claims['sub'], 'email' => $claims['email'],
                'given_name' => is_string($claims['given_name'] ?? null) ? $claims['given_name'] : '',
                'family_name' => is_string($claims['family_name'] ?? null) ? $claims['family_name'] : ''];
    }
    return null;
}
