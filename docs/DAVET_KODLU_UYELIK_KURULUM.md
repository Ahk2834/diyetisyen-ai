# Diyetisyen AI — Google giriş + tek kullanımlık davet kodu (hazırlık)
**Hedef:** cPanel PHP 8.3 / MySQL; React/Vite. **Canlı sisteme henüz uygulanmadı.**

## 1. Ön koşullar ve güvenlik
- Tam MySQL yedeği alın. Önce test veritabanında uygulayın.
- Mevcut `app_users` tablosunda `google_sub` ve `pending` durumu bulunmalıdır.
- `database/migrations/002_invitation_activation.sql` dosyasını yalnızca bir kez çalıştırın.
- Google doğrulamasında `iss`, `aud`, `exp`, `email_verified`, `sub` ve RS256 imzası kontrol edilir. Bunları kaldırmayın.
- Eski PHP dosyalarının canlıdaki kopyalarını yedekleyin. Canlı sunucudaki çalışan `google_token_verify.php` dosyası, GitHub'daki sürümle karşılaştırılmalıdır.
- `db_config.php` ve diğer gizli yapılandırmalar `public_html` dışında kalmalıdır.

## 2. Dosya eşleştirmeleri
- `server/public/api/google-auth.php` → `public_html/diyetisyen-ai/api/google-auth.php`
- `server/public/api/google-session.php` → `public_html/diyetisyen-ai/api/google-session.php`
- `server/private/google_account_service.php` → `/home2/yenilikc/diyetai_private/google_account_service.php`
- `server/private/google_token_verify.php` → `/home2/yenilikc/diyetai_private/google_token_verify.php`
- `server/private/google_session.php` → `/home2/yenilikc/diyetai_private/google_session.php`
- `hosting/private/invitation_generate.php` → `/home2/yenilikc/diyetai_private/invitation_generate.php` (yalnızca CLI)
- Frontend: `npm ci` (lockfile varsa) veya `npm install`, ardından `npm test` ve `npm run build`. `dist/` içeriği hedef `public_html/diyetisyen-ai/` klasörüne yüklenir. `api/` klasörünü silmeyin.

## 3. Davet kodu üretimi
**Terminal/SSH erişimi yoksa:** Önce `database/migrations/002_invitation_activation.sql` çalıştırın. Ardından phpMyAdmin SQL sekmesinde `database/migrations/003_create_invitation_phpmyadmin.sql` dosyasını çalıştırın; sonuçta tek kullanımlık kod görüntülenir. Kodun yalnızca SHA-256 özeti veritabanında saklanır.\n\nTerminal olan başka ortamlarda, **public_html dışında**:
```bash
php /home2/yenilikc/diyetai_private/invitation_generate.php
```
Çıkan 32 karakterlik kod yalnızca bir kez görüntülenir. Kodun SHA-256 özeti MySQL'e kaydedilir. Kodu sadece davet edilecek kullanıcıyla güvenli yoldan paylaşın. Otomatik e-posta gönderimi yoktur. Kod 30 gün geçerlidir.

Terminal gerekmiyor; phpMyAdmin SQL yöntemi yeterlidir. Kodları sorgu çıktısından güvenli şekilde alın ve sorgu geçmişinde görünür olabileceğini dikkate alın.

## 4. Kabul testleri
1. Google hesabı doğrulansın; yeni kullanıcı `pending` görsün.
2. Hatalı davet kodu kabul edilmesin.
3. Geçerli kod hesabı `active` yapsın ve güvenli oturum açsın.
4. Aynı kod ikinci hesapta çalışmasın.
5. Aktif hesap tekrar Google ile girişte kod istemesin.
6. Profilde ad, soyad ve e-posta görüntülensin.
7. Oturumu kapatıp `google-session.php` ile oturumun kapandığı doğrulansın.
8. İptal edilen/devre dışı kullanıcı giriş yapamasın.
9. Şifresiz Google hesabının e-posta/parola yoluyla oturum açamadığı doğrulansın.
10. Yanlış `Origin` başlıklı POST reddedilsin.

## 5. Temizlik
- Geçici `google-debug.log` dosyasını `public_html` altından silin.
- `google-auth.php` içine geçici eklenen `ini_set('error_log',...)` gibi tanılama satırlarını kaldırın.
- PHP günlüklerine Google ID token, davet kodu, şifre veya sağlık bilgisi yazmayın.

## Kapsam sınırı
Bu değişiklik **üyelik ve oturum** içindir; sağlık verilerinin cihazlar arasında güvenli senkronizasyonunu henüz sağlamaz. Canlı kurulum ve kabul testleri yapılmadan üretim hazır sayılmamalıdır.
