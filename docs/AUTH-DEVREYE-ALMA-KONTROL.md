# Diyetisyen AI — kimlik doğrulama devreye alma kontrolü

## Mevcut durum
- React web arayüzü cPanel üzerinde test amaçlı çalışıyor.
- PHP health.php GET isteğine yanıt veriyor.
- MySQL bağlantısı daha önce özel, geçici test ile doğrulandı.
- app_users ve app_sessions tabloları mevcut.
- auth_core.php özel dizinde; auth.php herkese açık API klasöründe.
- auth.php kayıt ve girişi bilinçli olarak reddediyor; kullanıcı hesabı açılmış değildir.

## Yayına açmadan önce yapılacaklar
1. API klasöründeki .htaccess kurallarını kontrol edin: eski GitHub sürümündeki FilesMatch kuralı health.php dışındaki PHP dosyalarını engeller. auth.php için gereken erişim değişikliğini güvenlik incelemesi tamamlanmadan uygulamayın.
2. Kimlik doğrulama işlemlerini ayrı servis katmanında geliştirin; sabit şifre, veritabanı bilgisi veya oturum anahtarı kod deposuna konulmamalıdır.
3. Giriş denemesi sınırları ve hesap bazlı kötüye kullanım koruması ekleyin.
4. Oturum anahtarlarını tarayıcı JavaScript'ine vermeyin. Secure, HttpOnly, SameSite çerezlerini, CSRF savunmasını ve oturum yenilemeyi uygulayın.
5. Parola sıfırlama, hesap kilitleme/askıya alma ve e-posta doğrulama akışlarını tamamlayın.
6. Sağlık verileri için KVKK aydınlatması, açık rıza gerekliliği analizi, veri minimizasyonu, erişim ve silme mekanizmalarını ayrı değerlendirin.
7. Üretim öncesinde test kullanıcıları ve sentetik verilerle bağımsız güvenlik testi yapın.

## Önemli
Bu aşamada yeni SQL çalıştırmak, mevcut db_config.php dosyasını değiştirmek veya yeni PHP dosyasını cPanel'e kopyalamak gerekmiyor. Gerçek sağlık verilerini sisteme girmeyin.
