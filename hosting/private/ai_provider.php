<?php
declare(strict_types=1);
/**
 * Provider adapter. Configuration belongs outside public_html.
 * Expected settings: enabled, endpoint, api_key, model.
 */
function dietai_provider_answer(string $message,array $settings): array {
    if (($settings['enabled'] ?? false) !== true) {
        return ['ok'=>false,'error'=>'ai_not_configured'];
    }
    $url=$settings['endpoint'] ?? '';
    $key=$settings['api_key'] ?? '';
    $model=$settings['model'] ?? '';
    if (!is_string($url)||!is_string($key)||!is_string($model)
        ||$key===''||$model===''||!preg_match('~^https://[a-zA-Z0-9.-]+(?::443)?/[^\\s]*$~D',$url)) {
        return ['ok'=>false,'error'=>'ai_not_configured'];
    }
    $host=parse_url($url,PHP_URL_HOST);
    $allowed=$settings['allowed_host'] ?? '';
    if (!is_string($host)||!is_string($allowed)||!hash_equals($allowed,$host)) {
        return ['ok'=>false,'error'=>'ai_not_configured'];
    }
    if (!function_exists('curl_init')) return ['ok'=>false,'error'=>'provider_unavailable'];
    $payload=json_encode([
      'model'=>$model,
      'messages'=>[
        ['role'=>'system','content'=>'Türkçe yanıt ver. Genel beslenme eğitimi sun. Tanı koyma, ilaç veya takviye dozu önerme, hastalık tedavisi veya kişiye özel klinik diyet yazma. Acil belirtilerde sağlık hizmetine yönlendir. Kısa ve anlaşılır ol.'],
        ['role'=>'user','content'=>$message]
      ],
      'max_tokens'=>500,
      'temperature'=>0.3
    ],JSON_UNESCAPED_UNICODE);
    if (!is_string($payload)) return ['ok'=>false,'error'=>'provider_unavailable'];
    $ch=curl_init($url);
    curl_setopt_array($ch,[
      CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
      CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$key],
      CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,
      CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
      CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
    ]);
    $response=curl_exec($ch);
    $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    if (!is_string($response)||$status!==200||strlen($response)>50000) {
        return ['ok'=>false,'error'=>'provider_unavailable'];
    }
    $decoded=json_decode($response,true);
    $answer=$decoded['choices'][0]['message']['content'] ?? null;
    if (!is_string($answer)||trim($answer)==='') return ['ok'=>false,'error'=>'provider_unavailable'];
    return ['ok'=>true,'answer'=>mb_substr($answer,0,3000)];
}
