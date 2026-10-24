# Diyetisyen AI — hızlı ve güvenli Google üyelik devreye alma

## Mevcut durum (24 Ekim 2026)
- PHP 8.3 ve openssl/curl/json etkin.
- `server/private/google_token_verify.php` ve `google_account_service.php` özel klasöre elle aktarıldı.
- `server/public/api/google-auth.php` canlı API klasörüne elle aktarıldı.
- GET için `method_not_allowed`, boş POST için `invalid_request` görüldü.
- PHP sözdizimi kontrolleri Codespaces üzerinde geçti.
- **Gerçek Google ID token, canlı veritabanı kaydı, yönetici onayı ve oturum açma henüz uçtan uca test edilmedi.**
- `GOOGLE_LOGIN_ENABLED=false` kalmalı; yalnızca frontend derlemesi yeterli değildir.

## Onay akışı (minimum uygulanabilir, ayrı admin paneli gerektirmeyen ilk aşama)
1. Google token sunucuda doğrulanır.
2. İlk başvuruda `app_users.status='pending'` ve `google_sub` saklanır. Oturum verilmez.
3. Yönetici yalnızca phpMyAdmin üzerinden başvuruyu inceler. Kişinin gerçekten davetli olduğunu ayrı kanaldan teyit eder.
4. Yönetici **belirli id ve google_sub** için `status='active'` yapar. Toplu onay verilmez.
5. Giriş için ayrıca sunucuda güvenli oturum çerezi oluşturma, oturum kontrolü ve çıkış işlevi tamamlanmalıdır. Şimdiki endpoint aktif kullanıcıya `session_not_enabled` döner.
6. Uygulama canlıya açılmadan önce gerçek Google hesabıyla başvuru, onay, oturum, çıkış, devre dışı bırakma ve CSRF testleri gerekir.

## phpMyAdmin: inceleme (salt okunur)
```sql
SELECT id, email, status, created_at
FROM app_users
WHERE google_sub IS NOT NULL
ORDER BY created_at DESC
LIMIT 50;
```

## phpMyAdmin: yalnızca doğrulanmış tek başvuruyu onaylama
`123` örnektir. Gerçek id ve google_sub değerini phpMyAdmin'de doğrulayın.
```sql
UPDATE app_users
SET status = 'active'
WHERE id = 123 AND google_sub = 'GOOGLE_SUB_BURAYA'
  AND status = 'pending';
```
Etkilenen satır sayısı tam 1 olmalı. Bu işlem henüz oturum açtırmaz.

## Güvenlik ve işletim
- Google Client Secret tarayıcıya, GitHub'a veya sohbete gönderilmez; bu GIS ID token akışında gerekmez.
- Sağlık profili verileri Google'a gönderilmez. Kullanıcı onayı olmadan sunucuya eşitleme yapılmaz.
- Yönetici onayını e-posta veya istemci tarafı bir bayrakla otomatikleştirmeyin.
- Giriş istekleri için sunucu tarafında hız sınırı ve güvenli oturum kontrolü henüz tamamlanmadı.
- Üretim için PHP dosyaları `php -l` ile sunucuda da doğrulanmalı.
- Google ücretsiz kimlik doğrulama hizmeti kullanılır; ücretli Cloud/Firebase hizmeti etkinleştirilmez.
