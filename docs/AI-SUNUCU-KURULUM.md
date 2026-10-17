# Gemini API kurulum güncellemesi

Diyetisyen AI artık Google Gemini `generateContent` biçimini kullanır. Önceki OpenAI-uyumlu `endpoint` ve `allowed_host` ayarları kullanılmaz.

Özel `/home2/yenilikc/diyetai_private/ai_settings.php` dosyası aşağıdaki alanları içeren bir PHP dizisi döndürmelidir:

- `enabled`: testler tamamlanana kadar `false`
- `api_key`: Google AI Studio üzerinden alınan gizli Gemini API anahtarı
- `model`: AI Studio'da hesabınızda erişilebilir, ücretsiz kotası bulunan modelin **tam kimliği**
- `rate_limit_secret`: en az 32 karakter rastgele sır

Anahtarı asla GitHub'a, uygulama arayüzüne veya Android APK'ya eklemeyin. `hosting/private/ai_provider.php` dosyasını özel sunucu dizinine ayrıca yükleyin. Gemini ücretsiz katmanında mesajların Google tarafından ürün geliştirme amacıyla kullanılabilme ihtimalini kullanıcılara açıkça bildirin; kayıtlı sağlık profilini otomatik göndermeyin. Üretim öncesi KVKK ve sağlayıcı koşullarını değerlendirin.

---

# Diyetisyen AI sunucu bağlantısı — kurulum kontrolü

Bu entegrasyon varsayılan olarak **kapalıdır**. GitHub kodunun başarılı derlenmesi canlı yapay zekâ hizmetinin çalıştığı anlamına gelmez.

1. cPanel'de herkese açık olmayan `/home2/yenilikc/diyetai_private/` dizinine `hosting/private/ai_provider.php` ve `hosting/private/ai_rate_limit.php` dosyalarını yerleştirin. `ai_rate_limits` adında PHP kullanıcısının yazabildiği, web üzerinden erişilemeyen bir dizin oluşturun.
2. Aynı özel dizinde `ai_settings.php` dosyası oluşturun. **Bu dosyayı GitHub'a, public_html'e veya Android paketine koymayın.**
3. Ayarlar bir PHP dizisi döndürmelidir: `enabled` (başlangıçta `false`), `endpoint` (HTTPS, OpenAI-uyumlu chat completions API), `allowed_host` (endpoint'in tam host adı), `api_key` (sunucu sırrı), `model` (sağlayıcıdaki geçerli model), `rate_limit_secret` (en az 32 rastgele karakter).
4. `hosting/api/ai.php` dosyasını web dizinindeki `/diyetisyen-ai/api/ai.php` yoluna yerleştirin. HTTPS ve `https://yenilikciegitim.com` Origin denetimi yapılır.
5. Sağlayıcı sözleşmesini, sağlık verisi aktarımını, KVKK aydınlatmasını ve saklama koşullarını kontrol edin. Sohbette kullanıcı tarafından yazılan metin harici sağlayıcıya iletilir; kayıtlı sağlık profili otomatik eklenmez.
6. Anahtarlar ve izinler hazırlandıktan sonra önce test ortamında deneyin; yalnızca kontroller tamamlanırsa `enabled => true` yapın.

**Kısıtlar:** IP başına saatte en fazla 10 istek, 1200 karakter mesaj, 20 saniye sağlayıcı zaman aşımı. Bu, üretim güvenliği için tek başına yeterli değildir: bot koruması, harcama kotası, servis sağlayıcı denetimi ve kapsamlı güvenlik incelemesi gerekir. Otomatik hastalık tedavisi veya kişiye özel klinik diyet sunulmaz.
