# Diyetisyen AI — cPanel test kurulumu

Bu paket **yalnızca React/Vite statik arayüzüdür**. PHP/MySQL bağlantısı, sunucu oturum yönetimi, şifreli sağlık kaydı veya üretim güvenliği içermez. Gerçek kişisel sağlık verisiyle kullanmayın.

## Kurulum (mevcut WordPress'e dokunmadan)

1. GitHub > Actions > **cPanel Hosting Package** > en yeni başarılı çalışmanın **Artifacts** bölümünden `diyetisyen-ai-cpanel-site` dosyasını indirin.
2. İndirilen dış ZIP'i açın. İçindeki `diyetisyen-ai-cpanel.zip` dosyası gerçek site paketidir.
3. cPanel > Dosya Yöneticisi'nde **mevcut sitenin gerçek document root** dizinine gidin (genellikle `public_html`, fakat alan adı yapılandırmasına göre farklı olabilir). Burada `diyetisyen-ai` adlı **yeni bir klasör** açın.
4. `diyetisyen-ai-cpanel.zip` dosyasını bu yeni klasöre yükleyin ve **aynı klasörde** çıkartın. `index.html` ve `assets/` doğrudan `diyetisyen-ai/` altında olmalı; fazladan `dist/` klasörü olmamalı.
5. Alan adınızın `https://ALAN-ADINIZ/diyetisyen-ai/` adresini açıp sayfanın yüklenmesini kontrol edin.

**Önemli:** WordPress'in `.htaccess` dosyasını değiştirmeyin. `/home2/yenilikc/diyetai_private` dizinine hiçbir web dosyası koymayın. Mevcut MySQL kullanıcı adı/şifrelerini veya `db_config.php` dosyasını ZIP'e eklemeyin.

Bu paket bir **test arayüzüdür**. Profil verileri tarayıcıda şifrelenmeden saklanabilir; ortak cihazlarda kullanmayın. Yayına alma ile güvenli hesap ve veritabanı entegrasyonu farklı geliştirme aşamalarıdır.
