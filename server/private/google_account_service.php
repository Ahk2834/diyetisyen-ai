<?php
declare(strict_types=1);

/** Requires a Google identity whose JWT signature and claims were verified. */
function dietai_google_apply_verified_identity(PDO $pdo, array $identity, ?string $invitationCode = null): array {
    $sub = $identity['sub'] ?? null;
    $email = $identity['email'] ?? null;
    if (!is_string($sub) || !preg_match('/^[0-9]{1,255}$/D', $sub) ||
        !is_string($email) || strlen($email) > 254 ||
        filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('Invalid verified identity');
    }
    $firstName = substr(trim((string)($identity['given_name'] ?? '')), 0, 100);
    $lastName = substr(trim((string)($identity['family_name'] ?? '')), 0, 100);
    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare('SELECT id,status FROM app_users WHERE google_sub=:sub LIMIT 1 FOR UPDATE');
        $find->execute(['sub'=>$sub]);
        $user = $find->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $emailCheck = $pdo->prepare('SELECT id FROM app_users WHERE email=:email LIMIT 1');
            $emailCheck->execute(['email'=>$email]);
            if ($emailCheck->fetch()) {
                $pdo->commit();
                return ['status'=>'account_link_required'];
            }
            $insert = $pdo->prepare("INSERT INTO app_users (email,google_sub,password_hash,status,first_name,last_name) VALUES (:email,:sub,NULL,'pending',:first,:last)");
            $insert->execute(['email'=>$email,'sub'=>$sub,'first'=>$firstName,'last'=>$lastName]);
            $user = ['id'=>(int)$pdo->lastInsertId(),'status'=>'pending'];
        }
        $userId = (int)$user['id'];
        $status = (string)$user['status'];
        if ($status === 'disabled') {
            $pdo->commit();
            return ['status'=>'disabled'];
        }
        if ($status === 'active') {
            $pdo->commit();
            return ['status'=>'active','user_id'=>$userId];
        }
        if ($invitationCode === null || $invitationCode === '') {
            $pdo->commit();
            return ['status'=>'pending'];
        }
        if (!preg_match('/^[A-Fa-f0-9]{32}$/D', $invitationCode)) {
            $pdo->commit();
            return ['status'=>'invalid_invitation'];
        }
        $codeHash = hash('sha256', strtolower($invitationCode));
        $findCode = $pdo->prepare('SELECT id FROM app_invitation_codes WHERE code_hash=:hash AND used_by IS NULL AND (expires_at IS NULL OR expires_at>UTC_TIMESTAMP()) LIMIT 1 FOR UPDATE');
        $findCode->execute(['hash'=>$codeHash]);
        $code = $findCode->fetch(PDO::FETCH_ASSOC);
        if (!$code) {
            $pdo->commit();
            return ['status'=>'invalid_invitation'];
        }
        $useCode = $pdo->prepare('UPDATE app_invitation_codes SET used_by=:user_id,used_at=UTC_TIMESTAMP() WHERE id=:id AND used_by IS NULL');
        $useCode->execute(['user_id'=>$userId,'id'=>(int)$code['id']]);
        if ($useCode->rowCount() !== 1) throw new RuntimeException('Invitation already used');
        $activate = $pdo->prepare("UPDATE app_users SET status='active',first_name=:first,last_name=:last WHERE id=:id AND status='pending'");
        $activate->execute(['first'=>$firstName,'last'=>$lastName,'id'=>$userId]);
        if ($activate->rowCount() !== 1) throw new RuntimeException('Account activation failed');
        $pdo->commit();
        return ['status'=>'active','user_id'=>$userId];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
