# Diyetisyen AI — Demo v0.1

Türkçe, mobil uyumlu, GitHub Pages için hazırlanmış React + TypeScript sağlıklı yaşam uygulaması.

## Çalışan modüller
- Ana panel ve örnek günlük menü
- Günlük/haftalık/30 günlük menü görünümü; alerjen filtresi ve öğün alternatifi
- Yaklaşık örnek tarif/makro değerleri ve aranabilir yemek listesi
- Alışveriş listesi ve işaretleme
- Günlük su kaydı (yalnızca günün toplamı localStorage içinde), hedef ayarı
- Genel egzersiz kartları ve sağlık bilgisi kartları
- Geçici profil; profil verileri AI modülüne bağlanmaz
- Bağlantı yapılandırılınca manuel AI sohbeti; açık gönderim onayı

## Sınırlamalar
**Bu bir klinik ürün değildir.** Menülerin besin değerleri örnek/tahminidir, TÜRKOMP ile doğrulanmış veri seti değildir. Kişiye özgü enerji hesabı, hastalık tedavisi diyeti, otomatik ilaç etkileşimi, bildirim servisi, güvenli danışan hesabı ve diyetisyen onay paneli yoktur. Bu işlevler varmış gibi sunulmaz. Sağlık profili sadece React belleğindedir; sayfa yenilemesinde kaybolur. Günlük su toplamı tarayıcıda saklanır. Gerçek sağlık verileriyle klinik kullanım için uygunluk ve güvenlik denetimi gerekir.

## Yerelde çalıştırma
Node 22 önerilir.
```bash
npm install
npm run dev
npm run build
```

## GitHub Pages
1. GitHub'da `diyetisyen-ai` adlı public repository açın.
2. Bu klasörün içindeki dosyaları, gizli `.github/workflows/deploy.yml` dahil, deponun köküne yükleyin. API anahtarı yüklemeyin.
3. `main` dalına gönderin.
4. `Settings → Pages → Build and deployment → Source: GitHub Actions` seçin.
5. Actions sekmesindeki `Deploy GitHub Pages` tamamlandığında `https://KULLANICI_ADI.github.io/diyetisyen-ai/` adresini açın.
6. Repository adı farklıysa Vite yapılandırması `GITHUB_REPOSITORY` değişkeninden otomatik okur.

## Opsiyonel AI kurulumu
AI **varsayılan olarak kapalıdır**. Gerçek API bağlantısı için:
1. Cloudflare Workers hesabı açın, `worker` klasöründen Worker yayımlayın.
2. `wrangler.toml` içindeki `ALLOWED_ORIGIN` değerini kendi `https://KULLANICI_ADI.github.io` origin'inizle değiştirin (sonunda `/` olmamalı).
3. `GEMINI_MODEL` değerini sağlayıcının güncel, kullanılabilir ve ücretsiz kotaya uygun model kimliğiyle değiştirin; ücretsiz katman koşullarını ve veri işleme politikasını kontrol edin.
4. `GEMINI_API_KEY` anahtarını Worker **secret** olarak ekleyin (`npx wrangler secret put GEMINI_API_KEY`); asla repoya eklemeyin.
5. Worker Rate Limiting binding'inizin Cloudflare planında desteklendiğini doğrulayın. Rate limiter yapılandırılmadan Worker güvenli biçimde 503 döner.
6. GitHub `Settings → Secrets and variables → Actions → Variables` içinde `VITE_AI_WORKER_URL` değişkenine yayımlanan Worker URL'sini ekleyin.
7. Yeniden deploy edin; yalnızca kullanıcının yazdığı mesaj gönderilir. Profil nesnesi istek gövdesine eklenmez.

**Önemli:** AI sağlayıcısı mesajları kendi politikalarına göre işleyebilir. Ücretsiz katman, sağlık verileri için uygun olmayabilir. Gerçek hassas verileri AI'ye göndermeden önce hukuki değerlendirme yapın. Worker'a erişim kontrolü olarak yalnızca CORS yeterli değildir; üretim öncesinde kimlik doğrulama, sağlam kötüye kullanım ve maliyet kontrolleri, kayıt politikaları ve KVKK denetimi gerekir. Bu demo herkese açık üretim sohbeti için hazır değildir.

## Bilimsel kaynaklar
T.C. Sağlık Bakanlığı TÜBER 2022, Türkiye Fiziksel Aktivite Rehberi, TÜRKOMP ve WHO sağlıklı beslenme yayınları başlangıç referanslarıdır. Klinik iddialar ve tam besin verileri henüz kaynak satırı bazında doğrulanmamıştır. Her kartın tekil kaynak eşlemesi gelecek sürüm kapsamındadır.

## Güvenlik
- `src/main.tsx` içindeki AI istek gövdesi yalnızca `{message}` içerir.
- Profil React belleğinde tutulur, AI bileşeni profile erişmek için herhangi bir API kullanmaz.
- Gizli anahtar yalnızca Worker ortamında bulunur.
- Worker mesaj gövdesini loglamaz (sağlayıcı/platform günlük politikalarını ayrıca kontrol edin).
- Her sayfa yenilemesinde sohbet geçmişi silinir.

## Lisans
Kod için MIT lisansı; içerik ve üçüncü taraf kaynak lisansları ayrıca doğrulanmalıdır.
