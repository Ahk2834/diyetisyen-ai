<?php
declare(strict_types=1);
/** Gemini generateContent adapter; credentials are supplied only by private settings. */
function dietai_provider_answer(string $message,array $settings,array $history=[]): array {
    if (($settings['enabled'] ?? false)!==true) return ['ok'=>false,'error'=>'ai_not_configured'];
    $key=$settings['api_key'] ?? null;
    $model=$settings['model'] ?? null;
    if (!is_string($key)||strlen($key)<16||!is_string($model)||
        !preg_match('/^[a-zA-Z0-9._-]{3,80}$/D',$model)) {
        return ['ok'=>false,'error'=>'ai_not_configured'];
    }
    if (!function_exists('curl_init')) return ['ok'=>false,'error'=>'provider_unavailable'];
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';
    $contents=[];
    foreach ($history as $turn) {
        $contents[]=['role'=>$turn['role'],'parts'=>[['text'=>$turn['text']]]];
    }
    $contents[]=['role'=>'user','parts'=>[['text'=>$message]]];
    $payload=json_encode([
      'systemInstruction'=>['parts'=>[['text'=>'Türkçe ve kısa yanıt ver. Yalnızca genel beslenme ve sağlıklı yaşam eğitimi sun. Tanı koyma, ilaç veya takviye dozu önerme, kişiye özel klinik diyet veya tedavi yazma. Acil belirtilerde acil sağlık hizmetine yönlendir.']]],
      'contents'=>$contents,
      'generationConfig'=>['maxOutputTokens'=>4096,'temperature'=>0.3]
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if (!is_string($payload))return ['ok'=>false,'error'=>'provider_unavailable'];
    // Retry only transient upstream 503; never retry 400/401/403/404/429.
    // A single retry for upstream 503; other errors are not retried.
    $response=false;
    $status=0;
    for ($attempt=0; $attempt<2; $attempt++) {
        $ch=curl_init($url);
        if ($ch===false) return ['ok'=>false,'error'=>'provider_unavailable'];
        curl_setopt_array($ch,[
          CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
          CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-goog-api-key: '.$key],
          CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>25,
          CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
          CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
        ]);
        $response=curl_exec($ch);
        $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($status!==503 || $attempt===1) break;
        sleep(1);
    }
    if (!is_string($response)||$status!==200||strlen($response)>100000) {
        error_log('DiyetisyenAI Gemini HTTP status: '.$status);
        return ['ok'=>false,'error'=>'provider_unavailable'];
    }
    $decoded=json_decode($response,true);
    if (!is_array($decoded)) {
        error_log('DiyetisyenAI Gemini invalid JSON');
        return ['ok'=>false,'error'=>'provider_unavailable'];
    }
    $finish=$decoded['candidates'][0]['finishReason'] ?? 'unknown';
    if (!is_string($finish)) $finish='unknown';
    error_log('DiyetisyenAI Gemini HTTP status: 200; finishReason: '.substr($finish,0,40));
    $parts=$decoded['candidates'][0]['content']['parts'] ?? [];
    if (!is_array($parts))return ['ok'=>false,'error'=>'provider_unavailable'];
    $answer='';
    foreach($parts as $part){if(is_array($part)&&is_string($part['text']??null))$answer.=$part['text'];}
    if (trim($answer)==='') {
        error_log('DiyetisyenAI Gemini empty text; finishReason: '.substr($finish,0,40));
        return ['ok'=>false,'error'=>'provider_unavailable'];
    }
    return ['ok'=>true,'answer'=>mb_substr($answer,0,3000)];
}
