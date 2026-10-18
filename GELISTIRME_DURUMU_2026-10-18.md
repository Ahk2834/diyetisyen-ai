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
