<?php
/**
 * Plugin Name: Diet AI Güncelleyici
 * Version: 1.1.0
 * Description: Diet AI cPanel paketini WordPress panelinden yükler; dış GitHub ZIP'ini de tanır.
 */
if (!defined('ABSPATH')) exit;
add_action('admin_menu', function () {
 add_management_page('Diet AI Güncellemeleri','Diet AI Güncellemeleri','manage_options','dietai-updater',function () {
  if (!current_user_can('manage_options')) return;
  echo '<div class="wrap"><h1>Diet AI Güncellemeleri <small>v1.1.0</small></h1>';
  $msg=get_transient('dietai_result_'.get_current_user_id());
  if($msg){delete_transient('dietai_result_'.get_current_user_id());echo '<div class="notice notice-info"><p>'.esc_html($msg).'</p></div>';}
  echo '<p>GitHub Actions üzerinden indirdiğiniz dış ZIP veya içindeki diyetisyen-ai-TEK-PAKET.zip dosyasını seçebilirsiniz.</p><form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">';
  wp_nonce_field('dietai_upload');echo '<input type="hidden" name="action" value="dietai_upload"><input type="file" name="package" accept=".zip" required>';
  submit_button('Paketi denetle ve güncelle');echo '</form><p>Önce tam site ve veritabanı yedeği alın. Sadece güvenilir kendi GitHub paketinizi kullanın.</p></div>';
 });
});
add_action('admin_post_dietai_upload', function () {
 if(!current_user_can('manage_options')) wp_die('Yetkisiz erişim.');
 check_admin_referer('dietai_upload');
 $zip=null;$inner=null;$message='';
 try {
  if(!class_exists('ZipArchive')) throw new RuntimeException('PHP ZipArchive etkin değil.');
  $f=$_FILES['package']??null;
  if(!$f||$f['error']!==UPLOAD_ERR_OK||!is_uploaded_file($f['tmp_name'])||$f['size']>30000000)throw new RuntimeException('ZIP yüklenemedi veya boyut sınırı aşıldı.');
  $zip=new ZipArchive();
  if($zip->open($f['tmp_name'],ZipArchive::CHECKCONS)!==true)throw new RuntimeException('ZIP dosyası okunamadı.');
  // GitHub Actions artifact ZIP'i içindeki gerçek dağıtım ZIP'ini otomatik aç.
  $nested=$zip->locateName('diyetisyen-ai-TEK-PAKET.zip',ZipArchive::FL_NOCASE);
  if($nested!==false){
   $raw=$zip->getFromIndex($nested);
   if($raw===false||strlen($raw)>30000000)throw new RuntimeException('İç ZIP okunamadı.');
   $inner=wp_tempnam('dietai-inner.zip');
   if(!$inner||file_put_contents($inner,$raw)!==strlen($raw))throw new RuntimeException('Geçici ZIP oluşturulamadı.');
   $zip->close();$zip=new ZipArchive();
   if($zip->open($inner,ZipArchive::CHECKCONS)!==true)throw new RuntimeException('İç ZIP geçersiz.');
  }
  $files=[];$bytes=0;$prefix='public_html/diyetisyen-ai/';
  if($zip->numFiles>2500)throw new RuntimeException('ZIP çok fazla dosya içeriyor.');
  for($i=0;$i<$zip->numFiles;$i++){
   $stat=$zip->statIndex($i);$path=ltrim($stat['name'],'./');
   if(substr($path,-1)==='/')continue;
   if(strpos($path,$prefix)!==0)continue;
   $rel=substr($path,strlen($prefix));
   if(!$rel||strpos($rel,'..')!==false||strpos($rel,'\\')!==false||!preg_match('~^[a-zA-Z0-9_./-]+$~D',$rel))throw new RuntimeException('Geçersiz dosya yolu.');
   if(!preg_match('~^(?:assets/|api/|icons/|index\.html$|sw\.js$|manifest\.webmanifest$|favicon\.svg$|[a-zA-Z0-9_-]+/)~',$rel))throw new RuntimeException('İzin verilmeyen yol: '.$rel);
   $mode=($stat['external_attributes']??0)>>16;
   if(($mode&0170000)===0120000)throw new RuntimeException('Sembolik bağlantı engellendi.');
   if(isset($files[$rel]))throw new RuntimeException('Tekrarlı dosya yolu.');
   $bytes+=(int)$stat['size'];if($bytes>100000000)throw new RuntimeException('Açılmış paket boyutu sınırı aşıldı.');
   $files[$rel]=$i;
  }
  if(!isset($files['index.html'],$files['api/google-auth.php'],$files['api/google-session.php'])){
   throw new RuntimeException('Paket yapısı uyuşmuyor. Bulunan örnek dosyalar: '.implode(', ',array_slice(array_keys($files),0,8)).'. Toplam: '.count($files));
  }
  $target=trailingslashit(ABSPATH).'diyetisyen-ai';
  if(!is_dir($target)||is_link($target))throw new RuntimeException('Hedef Diet AI klasörü bulunamadı.');
  $backup=WP_CONTENT_DIR.'/dietai-update-backups/'.gmdate('YmdHis').'-'.wp_generate_password(6,false,false);
  if(!wp_mkdir_p($backup))throw new RuntimeException('Yedek oluşturulamadı.');
  file_put_contents(dirname($backup).'/.htaccess',"Require all denied\nDeny from all\n");
  file_put_contents(dirname($backup).'/index.php','<?php exit;');
  $manifest=[];
  foreach($files as $rel=>$i){
   $dest=$target.'/'.$rel;$parent=dirname($dest);
   for($p=$parent;$p!==$target&&$p!==dirname($target);$p=dirname($p))if(is_link($p))throw new RuntimeException('Sembolik klasör engellendi.');
   if(is_link($dest))throw new RuntimeException('Sembolik dosya engellendi.');
   $manifest[$rel]=is_file($dest);
   if($manifest[$rel]){
    $copy=$backup.'/files/'.$rel;
    if(!wp_mkdir_p(dirname($copy))||!copy($dest,$copy))throw new RuntimeException('Yedekleme başarısız.');
   }
  }
  file_put_contents($backup.'/manifest.json',wp_json_encode($manifest));
  $written=[];
  try {
   foreach($files as $rel=>$i){
    $dest=$target.'/'.$rel;
    if(!wp_mkdir_p(dirname($dest)))throw new RuntimeException('Hedef klasör oluşturulamadı.');
    $data=$zip->getFromIndex($i);if($data===false)throw new RuntimeException('ZIP okunamadı.');
    $tmp=tempnam(dirname($dest),'.dietai');
    if($tmp===false||file_put_contents($tmp,$data,LOCK_EX)!==strlen($data)||!rename($tmp,$dest))throw new RuntimeException('Dosya yazılamadı: '.$rel);
    $written[]=$rel;
   }
  } catch(Throwable $e) {
   foreach(array_reverse($written) as $rel){
    if($manifest[$rel])@copy($backup.'/files/'.$rel,$target.'/'.$rel);
    else @unlink($target.'/'.$rel);
   }
   throw $e;
  }
  $message='Başarılı: '.count($written).' dosya güncellendi. Dosya yedeği: '.basename($backup);
 } catch(Throwable $e){$message='Hata: '.$e->getMessage();}
 finally {if($zip instanceof ZipArchive)$zip->close();if($inner&&file_exists($inner))@unlink($inner);}
 set_transient('dietai_result_'.get_current_user_id(),$message,120);
 wp_safe_redirect(admin_url('tools.php?page=dietai-updater'));exit;
});
