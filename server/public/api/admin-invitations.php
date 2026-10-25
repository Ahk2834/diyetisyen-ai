<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function reply(int $status,array $data):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
$method=$_SERVER['REQUEST_METHOD']??'';
if(!in_array($method,['GET','POST'],true))reply(405,['ok'=>false]);
if($method==='POST'){
 $origin=$_SERVER['HTTP_ORIGIN']??'';
 $site=$_SERVER['HTTP_SEC_FETCH_SITE']??'';
 if($origin!=='https://yenilikciegitim.com'||($site!==''&&$site!=='same-origin'))reply(403,['ok'=>false]);
 if(!str_starts_with($_SERVER['CONTENT_TYPE']??'','application/json'))reply(415,['ok'=>false]);
}
try{
 require_once '/home2/yenilikc/diyetai_private/auth_core.php';
 require_once '/home2/yenilikc/diyetai_private/google_session.php';
 $pdo=auth_db();$user=dietai_session_user($pdo);
 if(!$user)reply(401,['ok'=>false]);
 // Authorization comes exclusively from server configuration, never the browser.
 $config=require '/home2/yenilikc/diyetai_private/admin_config.php';
 $admins=$config['admin_emails']??[];
 if(!is_array($admins)||!in_array(strtolower((string)$user['email']),array_map('strtolower',$admins),true))reply(403,['ok'=>false]);
 if($method==='GET'){
  $stmt=$pdo->query("SELECT c.id,c.created_at,c.expires_at,c.used_at,c.revoked_at,u.email AS used_by_email FROM app_invitation_codes c LEFT JOIN app_users u ON u.id=c.used_by ORDER BY c.id DESC LIMIT 100");
  reply(200,['ok'=>true,'codes'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
 }
 $raw=file_get_contents('php://input');
 if(strlen($raw)>2048)reply(413,['ok'=>false]);
 $body=json_decode($raw,true);
 if(!is_array($body))reply(400,['ok'=>false]);
 $action=$body['action']??'';
 if($action==='create'){
  $code=bin2hex(random_bytes(16));
  $stmt=$pdo->prepare('INSERT INTO app_invitation_codes(code_hash,expires_at) VALUES (:hash,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 DAY))');
  $stmt->execute(['hash'=>hash('sha256',$code)]);
  reply(201,['ok'=>true,'code'=>$code]);
 }
 if($action==='revoke'){
  $id=filter_var($body['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
  if(!$id)reply(400,['ok'=>false]);
  $stmt=$pdo->prepare('UPDATE app_invitation_codes SET revoked_at=UTC_TIMESTAMP() WHERE id=:id AND used_by IS NULL AND revoked_at IS NULL');
  $stmt->execute(['id'=>$id]);
  reply(200,['ok'=>true,'revoked'=>$stmt->rowCount()===1]);
 }
 reply(400,['ok'=>false]);
}catch(Throwable $e){error_log('DietAI admin invitations: '.get_class($e));reply(503,['ok'=>false]);}
