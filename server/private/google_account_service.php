<?php
declare(strict_types=1);

/**
 * Private Google sign-in application service.
 * This module NEVER creates an authenticated session for pending users.
 * Must be called only after dietai_google_verify_id_token() succeeds.
 * Keep outside public_html and do not expose an endpoint until security tests pass.
 */
function dietai_google_apply_verified_identity(PDO $pdo, array $identity): array {
    $sub = $identity['sub'] ?? null;
    $email = $identity['email'] ?? null;
    if (!is_string($sub) || !preg_match('/^[0-9]{1,255}$/D', $sub) ||
        !is_string($email) || strlen($email) > 254 ||
        filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('Invalid verified identity');
    }

    // Never trust email alone to link an existing password account to Google.
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, status FROM app_users WHERE google_sub = :sub LIMIT 1 FOR UPDATE');
        $stmt->execute(['sub' => $sub]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($account)) {
            $status = (string) $account['status'];
            $pdo->commit();
            if ($status === 'active') return ['status' => 'active', 'user_id' => (int) $account['id']];
            return ['status' => $status === 'disabled' ? 'disabled' : 'pending'];
        }

        $emailCheck = $pdo->prepare('SELECT id FROM app_users WHERE email = :email LIMIT 1');
        $emailCheck->execute(['email' => $email]);
        if ($emailCheck->fetch(PDO::FETCH_ASSOC)) {
            // Account linking requires a separate, authenticated verification flow.
            $pdo->commit();
            return ['status' => 'account_link_required'];
        }

        $insert = $pdo->prepare(
            "INSERT INTO app_users (email, google_sub, password_hash, status)
             VALUES (:email, :sub, NULL, 'pending')"
        );
        $insert->execute(['email' => $email, 'sub' => $sub]);
        $pdo->commit();
        return ['status' => 'pending'];
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        // Concurrent first sign-in can race against the unique constraints.
        // Caller must return a generic error and retry safely.
        throw $exception;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
