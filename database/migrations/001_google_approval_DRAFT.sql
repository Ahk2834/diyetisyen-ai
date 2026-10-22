-- Diyetisyen AI: Google giriş + yönetici onayı için TASLAK veritabanı geçişi.
-- HENÜZ ÇALIŞTIRMAYIN. Önce tam yedek, gerçek tablo şeması ve test doğrulaması gerekir.
-- Yalnızca mevcut app_users yapısı (id, email, password_hash, status, created_at, updated_at)
-- için tasarlanmıştır. app_sessions tablosu değiştirilmez.
-- Mevcut parola hesapları korunur; Google ile giriş yapan hesaplar parolasız olabilir.
-- Google'ın sabit ve doğrulanmış 'sub' kimliği kullanılır; e-posta tek başına kimlik değildir.

ALTER TABLE app_users
  MODIFY COLUMN password_hash VARCHAR(255) NULL,
  MODIFY COLUMN status ENUM('active','disabled','pending') NOT NULL DEFAULT 'pending',
  ADD COLUMN google_sub VARCHAR(255) NULL AFTER email,
  ADD UNIQUE KEY uq_app_users_google_sub (google_sub);

-- ÖNEMLİ: Bu SQL yalnızca şema hazırlığıdır; kendi başına giriş veya onay sağlamaz.
-- Uygulama sunucuda Google ID token imzası, issuer, audience, expiry ve email_verified
-- kontrolü yapmadan bu alanları doldurmamalıdır.
-- Google ile ilk başvuru: pending. Yalnızca yetkili yönetici active yapabilir.
-- Yönetici yetkisi yalnızca istemciden gelen e-posta/role bilgisine güvenilerek verilmemeli.
-- Canlıya geçmeden önce OAuth test modu, CSRF, oturum çerezleri, hız sınırlama,
-- güvenli hata mesajları, KVKK ve cihazlar arası eşitleme testleri tamamlanmalıdır.
