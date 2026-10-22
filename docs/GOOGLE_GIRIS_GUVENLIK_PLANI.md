# Google giriş güvenlik planı (taslak, canlıya alınmadı)
Google OAuth Client ID: 255875796749-j08u7l1qmdad17qoi79e4eugtrlgsqv4.apps.googleusercontent.com
Google Identity Services yalnızca kimlik sağlayıcıdır; sağlık bilgileri Google'a gönderilmez.
Sunucu ID token imzasını Google JWKS anahtarlarıyla doğrulamalı; RS256, kid, issuer, audience, expiry, email_verified ve sabit sub kontrol edilmeli.
Yeni Google hesapları pending olmalı; onay verilmeden oturum ve sağlık verisi erişimi olmamalı.
Mevcut parola hesapları yalnızca e-posta eşleşmesine dayanılarak Google hesabına bağlanmamalı.
Yönetici yetkisi sunucu tarafında tanımlanmalı; istemciden gelen rol veya e-posta tek başına yeterli değildir.
Oturum anahtarı yalnızca Secure HttpOnly SameSite çerezinde taşınmalı; CSRF ve hız sınırlama uygulanmalı.
Mevcut localStorage verileri kullanıcı açık izin vermeden sunucuya taşınmamalı.
Google ücretli API, Firebase, Identity Platform veya faturalandırma açılmayacak.
Sonraki adım: cPanel PHP openssl, curl, json uzantılarını doğrula; ardından güvenli doğrulayıcı kodu ve testler.
Canlıya geçmeden uçtan uca test ve kullanıcı onayı zorunlu.