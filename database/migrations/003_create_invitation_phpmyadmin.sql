-- phpMyAdmin SQL sekmesinde, 002 geçişinden SONRA çalıştırın.
-- Bu komut her çalıştırıldığında YENİ bir tek kullanımlık kod üretir.
-- Çıktıdaki kodu bir defa kopyalayıp yalnızca davet ettiğiniz kişiyle paylaşın.
-- Kullanılmamış kodlar 30 gün sonra geçersiz olur.
SET @dietai_invite_code = LOWER(HEX(RANDOM_BYTES(16)));
INSERT INTO app_invitation_codes (code_hash, expires_at)
VALUES (SHA2(@dietai_invite_code, 256), DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY));
SELECT @dietai_invite_code AS tek_kullanimlik_davet_kodu;
SET @dietai_invite_code = NULL;
