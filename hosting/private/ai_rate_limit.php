<?php
declare(strict_types=1);
/** File-backed per-client rate limiter. Fails closed if secure storage is unavailable. */
function dietai_ai_allow_request(string $clientId,string $secret,string $directory,int $now): bool {
    if (strlen($secret)<32||$clientId===''||!is_dir($directory)||!is_writable($directory)) return false;
    $id=hash_hmac('sha256',$clientId,$secret);
    $path=rtrim($directory,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'ai-'.$id.'.json';
    $file=@fopen($path,'c+');
    if ($file===false)return false;
    if (!flock($file,LOCK_EX)){fclose($file);return false;}
    @chmod($path,0600);
    rewind($file);
    $raw=stream_get_contents($file,4096);
    $entries=json_decode(is_string($raw)?$raw:'',true);
    if (!is_array($entries))$entries=[];
    $entries=array_values(array_filter($entries,static fn($t):bool=>is_int($t)&&$t>$now-3600&&$t<=$now));
    $allowed=count($entries)<10;
    if ($allowed)$entries[]=$now;
    rewind($file);
    $ok=ftruncate($file,0)&&fwrite($file,json_encode($entries))!==false&&fflush($file);
    flock($file,LOCK_UN);
    fclose($file);
    return $allowed&&$ok;
}
