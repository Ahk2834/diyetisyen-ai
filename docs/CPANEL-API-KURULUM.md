# Diyetisyen AI — PHP API ilk kurulum

Bu adım mevcut çalışan React web sitesini **değiştirmez**. GitHub'daki `hosting/api/health.php` dosyasını cPanel Dosya Yöneticisi ile `public_html/diyetisyen-ai/api/health.php` konumuna kopyalayın. Aynı klasöre `hosting/api/.htaccess` dosyasını da ekleyin.

Beklenen test adresi: `https://yenilikciegitim.com/diyetisyen-ai/api/health.php`

Beklenen yanıt: `{"ok":true,"service":"diyetisyen-ai-api","version":"v1"}`

Bu kontrol veritabanına bağlanmaz, kimlik doğrulaması yapmaz ve hassas veri işlemez. Test başarılı olsa bile uygulama üretim ortamında kişisel sağlık verisi saklamak için hazır değildir.

**Özel sunucu dizini** `/home2/yenilikc/diyetai_private/` ve mevcut veritabanı bu aşamada değişmeden kalır. Kimlik bilgilerini GitHub'a, public_html'e veya ekran görüntülerine koymayın.
