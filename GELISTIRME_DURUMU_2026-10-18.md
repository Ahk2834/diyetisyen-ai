# Diyetisyen AI — geliştirme durumu (18 Ekim 2026)

## Kaydedilen değişiklikler
- Türk mutfağı besin kataloğu genişletildi; değerlerin önemli kısmı yaklaşık ve resmî olarak doğrulanmış değil.
- Tarif malzemelerinden kalori hesabı, porsiyon ve pişmiş ağırlık açıklamaları eklendi.
- 41 düzenlenebilir hazır yemek tarifi şablonu eklendi.
- Su takibi: hedef seçimi, profil bilgilerine dayalı isteğe bağlı başlangıç önerisi, ilerleme çubuğu ve kalan miktar gösterimi eklendi.
- Günlük/haftalık/aylık beslenme planı, besinler ve tariflerin kullanıcı tarafından çalıştığı bildirildi.

## Canlıya geçmeden önce zorunlu kontroller
1. `npm run build` başarılı olmalı; tarayıcıda menüler ve mobil görünüm test edilmeli.
2. Profil bazlı su hedefi kayıt/geri yükleme, günlük sıfırlama ve veri tutarlılığı doğrulanmalı.
3. **Cihazlar arası eşitleme HENÜZ YOK.** Su miktarı tarayıcı localStorage'ında, hedef de localStorage'ında saklanır. Farklı cihazlar ayrı veri gösterir.
4. Eşitleme için kullanıcı kimlik doğrulaması, sunucuda kullanıcıya özel güvenli PHP/MySQL API'si, yetkilendirme, tarih bazlı kayıt, hata/çakışma yönetimi ve cihazlar arası test gereklidir. Sağlık verileri kullanıcı onayı olmadan sunucuya aktarılmamalı.
5. Su hedefi tıbbi reçete değildir; özel hastalık/sıvı kısıtlaması halinde hekim önerisi esastır.
6. Üretim ortamına deploy edilmedi. `main` değiştirilmedi.

## Çalışma dalı
`full-v1-development` — güvenli geliştirme ve sonraki testler için.


## 19 Ekim 2026 — Google ile giriş / cihaz eşitleme durak noktası
- Kullanıcı, **Google ile üyelik başvurusu + yönetici onayı** modelini seçti (ilk etapta yaklaşık 20–30 kişi). Ayrıca telefonda ana ekrana eklenebilen PWA ve telefon-bilgisayar veri eşitleme istiyor.
- Canlı cPanel'de `public_html/diyetisyen-ai/api/auth.php` mevcut; incelenen içerikte `register` ve `login` eylemleri bilerek HTTP 503 ile devre dışı bırakılmış. Canlıya değişiklik yapılmadı.
- `public_html/diyetisyen-ai/api/ai.php` ayrı AI servisi; kimlik doğrulama dosyasıyla karıştırılmamalı.
- `public_html` dışındaki `diyetai_private` klasöründe `auth_core.php`, `db_config.php`, AI servis dosyaları mevcut. `auth_core.php` PDO MySQL bağlantısı, `password_hash` / `password_verify`, SHA-256 özetiyle 7 günlük rastgele oturum tokenı üretme/bulma/iptal etme fonksiyonlarını içeriyor. PHP session_start yok çünkü özel DB token sistemi kullanılıyor; çerez yönetimi henüz doğrulanmadı.
- phpMyAdmin'de `yenilikc_diyetai_test` veritabanı mevcut: `app_users` (id, email unique, password_hash, status, created_at, updated_at), `app_sessions` (id, user_id, token_hash unique, expires_at, created_at), `app_healthcheck`. Kullanıcı ve oturum tabloları boştu. Bunun canlı uygulamanın kullandığı DB olduğu ayrıca doğrulanmalı.
- Google Cloud Console'da **Diyetisyen AI** adlı proje başarıyla oluşturuldu. Ekranda görünen Project ID: `diyetisyen-ai-512112`. Ücretsiz deneme / $300 kredisi / faturalandırma etkinleştirilmedi; kullanıcı ücretli hizmet istemiyor.
- **Tam durak noktası:** Kullanıcıya önce sağ üst bildirimden **Select Project** seçmesi, sonra `https://console.cloud.google.com/auth/overview` sayfasını açıp Google Auth Platform ekran görüntüsü göndermesi söylendi. OAuth consent screen ve OAuth client henüz yapılandırılmadı.
- **Sonraki adım:** Google Auth Platform ekranını görüp tek tek OAuth yapılandırması; ücretli servis açmadan. Sonra güvenli Google ID token doğrulaması (sunucuda Google anahtarları/audience/issuer/exp), bekleyen kullanıcılar için yönetici onayı, güvenli HttpOnly Secure SameSite çerezli oturumlar, CSRF/origin/rate limit korumaları, profil verilerinin aktarımı için açık izin, su verileri için güvenli cihaz eşitleme, PWA manifest ve servis çalışanı. Yalnızca geliştirme dalında çalış; canlıya ve `main` dalına dokunma; güvenlik ve build testleri tamamlanmadan deploy yapma.
