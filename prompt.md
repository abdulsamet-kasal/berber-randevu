\# GÖREV: "berber-randevu" — Açık kaynaklı, profesyonel berber randevu web sitesi



Sen kıdemli bir full-stack geliştiricisin ve UI/UX tasarımcısısın. Bu projeyi sıfırdan, production kalitesinde ve açık kaynak olarak geliştireceksin. Aşağıdaki gereksinimlerin tamamını uygula. Belirsiz kalan her teknik ve tasarım kararını kendin al, gerekçesini README'ye yaz. Soru sormak yerine makul varsayımla ilerle; yalnızca GitHub erişimi veya Supabase kimlik bilgileri gibi senin tek başına çözemeyeceğin konularda dur ve bana sor.



\---



\## 1. PROJE TEMEL BİLGİLERİ



\- \*\*Proje adı:\*\* berber-randevu

\- \*\*Marka adı:\*\* "Berber Randevu" (site ve UI'da bu isim kullanılacak)

\- \*\*Dil:\*\* Arayüz tamamen Türkçe. Kod, değişken adları ve commit mesajları İngilizce olacak.

\- \*\*Kapsam:\*\* Tek şube, tek işletme.

\- \*\*Lisans:\*\* MIT. Repo kökünde `LICENSE` dosyası olacak; telif satırı `Copyright (c) 2026 berber-randevu contributors` olarak yazılacak.

\- \*\*Logo, renk paleti, tipografi, ikon seti:\*\* Sen belirleyeceksin. Berber temasına uygun, modern, güven veren ve sade bir kimlik oluştur. Logo'yu SVG olarak yap ve `public/assets/` altına koy. Renk değerlerini CSS değişkenleri olarak tanımla ve gerekçesini README'de yaz.

\- \*\*Ödeme:\*\* Yok. Ödeme, kapora, kart saklama veya ödeme sağlayıcısı entegrasyonu KESİNLİKLE eklenmeyecek.

\- \*\*Harici bildirim:\*\* SMS, WhatsApp ve e-posta gönderimi YOK. Tüm bildirimler sitenin içinde (uygulama içi / in-app) olacak.



\---



\## 2. GITHUB REPOSU VE SÜRÜM KONTROLÜ



1\. GitHub'da yeni bir \*\*public\*\* repo oluştur: `berber-randevu`. Açıklama: "Berber işletmeleri için açık kaynak randevu ve yönetim sistemi."

2\. Yalnızca \*\*`main`\*\* branch'i kullan. Başka branch açma.

3\. Repo köküne şunları ekle: `LICENSE` (MIT), `README.md`, `.gitignore` (PHP, Node, `.env`, `vendor/`, OS dosyaları), `.env.example`, `CHANGELOG.md`, `SECURITY.md`, `CONTRIBUTING.md`.

4\. Küçük ve anlamlı commit'ler at. Conventional Commits formatını kullan (`feat:`, `fix:`, `chore:`, `docs:`, `test:`).

5\. GitHub erişimi için kullanıcının verdiği token'ı \*\*yalnızca\*\* ortam değişkeninden oku. Token'ı hiçbir dosyaya, commit'e, README'ye veya log'a yazma.

6\. Repo oluşturamıyorsan dur ve bana bildir; tahmin yürütme.



\---



\## 3. AGENCY-AGENTS KULLANIMI



Proje boyunca `agency-agents` içindeki skill ve agent'ları kullan:



\- \*\*Tasarım:\*\* Görsel kimlik, renk sistemi, tipografi, bileşen tasarımı, mobil öncelikli layout ve erişilebilirlik (WCAG 2.1 AA) için ilgili tasarım skill'lerini kullan.

\- \*\*Frontend:\*\* Sayfa yapısı, bileşen kalitesi, responsive davranış, animasyon ve performans (Core Web Vitals) için frontend skill'lerini kullan.

\- \*\*Backend / PHP:\*\* Mimari, güvenlik, hata yönetimi ve test için backend skill'lerini kullan.

\- \*\*Veritabanı:\*\* Şema tasarımı, indeksleme, RLS ve migration için veritabanı skill'lerini kullan.

\- \*\*Güvenlik ve KVKK:\*\* Güvenlik kontrol listesi için ilgili skill'leri uygula.



Hangi skill'i nerede kullandığını `docs/AGENTS\_USED.md` dosyasında listele. Hangi skill'in bulunamadığını da not et ve en yakın alternatifi kullan.



\---



\## 4. TEKNOLOJİ YIĞINI



\- \*\*Backend:\*\* PHP 8.2+ (saf PHP veya hafif bir framework; önerim: Slim Framework veya kendi küçük MVC yapın, kararı sen ver ve gerekçelendir). Composer ile paket yönetimi.

\- \*\*Veritabanı ve kimlik:\*\* Supabase (PostgreSQL + Supabase Auth + Row Level Security).

\- \*\*Frontend:\*\* Sunucu taraflı render edilen PHP şablonları. Etkileşim için vanilla JavaScript veya Alpine.js gibi hafif bir kütüphane. Ağır SPA framework'ü KULLANMA. Stil için Tailwind CSS (derlenmiş) veya temiz bir CSS mimarisi.

\- \*\*PHP ↔ Supabase bağlantısı:\*\*

&#x20; - Randevu oluşturma ve çakışma kontrolü gibi transaction gerektiren işlemler için Supabase Postgres'e doğrudan PDO ile bağlan (pooler connection string kullan).

&#x20; - Auth ve basit okuma işlemleri için Supabase REST API'yi (PostgREST) kullanabilirsin.

&#x20; - `service\_role` anahtarı YALNIZCA sunucu tarafında kullanılacak; asla tarayıcıya veya public dosyalara sızmayacak.

\- \*\*Test:\*\* PHPUnit (birim ve entegrasyon), Playwright (uçtan uca, en az randevu akışı için).

\- \*\*Kod kalitesi:\*\* PSR-12, PHPStan seviye 6+, `composer lint` ve `composer test` komutları.

\- \*\*CI:\*\* GitHub Actions ile her push'ta lint, statik analiz ve testler çalışsın.



\---



\## 5. VERİTABANI ŞEMASI (SEN TASARLAYACAKSIN)



Şemayı sen tasarla ve `supabase/migrations/` altında sıralı SQL dosyaları olarak yaz. Şunları kapsamalı:



\*\*Olması gereken temel tablolar (gerekirse ekle):\*\*

\- `profiles`: Supabase Auth kullanıcısı ile eşleşen, rol (`admin` | `staff`) ve görünen ad.

\- `staff`: Berberler (ad, unvan, kısa biyografi, fotoğraf, aktiflik durumu, auth kullanıcısı ile ilişki).

\- `services`: Hizmetler (ad, açıklama, süre dakika, fiyat, kategori, aktiflik, sıra).

\- `staff\_services`: Hangi berber hangi hizmeti verir.

\- `working\_hours`: Berber bazlı haftalık çalışma saatleri ve molalar.

\- `time\_off`: İzin ve kapalı günler (tekil ve tarih aralığı).

\- `business\_settings`: İşletme bilgileri, adres, telefon, harita koordinatı, çalışma saatleri varsayılanı, iptal politikası süresi (saat cinsinden), randevu alma ön süresi, maksimum ileri tarih penceresi.

\- `customers`: Misafir müşteriler (ad, telefon, opsiyonel e-posta, KVKK onay kaydı referansı). Müşteri hesabı zorunlu DEĞİL.

\- `appointments`: Randevular. `staff\_id`, `customer\_id`, zaman aralığı (`tstzrange` veya başlangıç-bitiş), durum (`booked`, `completed`, `cancelled`, `no\_show`), `confirmation\_code`, müşteri notu, dahili not, oluşturulma zamanı, `idempotency\_key`.

\- `appointment\_services`: Randevuya bağlı hizmetler, o anki fiyat ve süre anlık görüntüsü ile (fiyat sonradan değişse bile geçmiş korunsun).

\- `gallery\_items`: Önce-sonra veya genel galeri görselleri, kategori, sıra, yayın durumu.

\- `reviews`: Yorumlar (puan, metin, yayın durumu, randevuya bağlı doğrulama).

\- `announcements`: Ana sayfada gösterilecek duyurular/kampanya metinleri.

\- `notifications`: Uygulama içi bildirimler (hedef kullanıcı, tür, başlık, mesaj, okundu zamanı, ilgili kayıt referansı).

\- `consents`: KVKK açık rıza ve aydınlatma kaydı (metin versiyonu, zaman, IP maskeli).

\- `audit\_logs`: Kritik değişikliklerin kaydı (kim, ne zaman, hangi tablo, eski/yeni değer özeti).

\- `rate\_limits` veya eşdeğeri: Randevu formu ve giriş için kötüye kullanım koruması.



\*\*Zorunlu veritabanı kuralları:\*\*

\- Aynı berber için çakışan randevuları engelleyen \*\*EXCLUDE kısıtı\*\* (`btree\_gist` extension ile, yalnızca `booked` durumundaki randevular için). Bu, yarış koşullarına karşı son savunma hattıdır.

\- Tüm zaman damgaları `timestamptz`, UTC olarak saklanacak. Gösterim `Europe/Istanbul`.

\- Uygun indeksler (berber + tarih, müşteri telefonu, durum).

\- Tüm tablolarda \*\*RLS aktif\*\*. Müşteri (anonim) yalnızca kendi randevusunu ve public verileri görebilsin. Staff yalnızca kendi randevularını, admin her şeyi görsün. Politikaları açıkça yaz ve README'de belgele.

\- `updated\_at` otomatik güncelleme trigger'ı.

\- Örnek seed verisi (`supabase/seed.sql`): 3 örnek berber, 8–10 örnek hizmet, örnek çalışma saatleri, örnek galeri ve yorum.



\---



\## 6. HALKA AÇIK SİTE



\*\*Sayfalar:\*\*

\- \*\*Ana sayfa:\*\* Hero alanı, "Randevu Al" CTA, öne çıkan hizmetler, duyurular, kısa "neden biz" bölümü, yorumlardan öne çıkanlar, konum özeti, sticky mobil randevu butonu.

\- \*\*Hizmetler ve fiyatlar:\*\* Kategorili liste, süre, fiyat, kısa açıklama.

\- \*\*Ekibimiz:\*\* Berber kartları (fotoğraf, unvan, biyografi, uzmanlık hizmetleri).

\- \*\*Galeri:\*\* Kategorili, lazy-load, optimize görseller (WebP), lightbox.

\- \*\*Yorumlar:\*\* Yalnızca onaylı yorumlar, puan özeti, sayfalama.

\- \*\*İletişim ve konum:\*\* Adres, telefon, çalışma saatleri, Google Maps gömülü harita (API anahtarı gerektirmeyen embed), yol tarifi bağlantısı.

\- \*\*Randevu al:\*\* Aşağıdaki akış (Bölüm 7).

\- \*\*Randevumu sorgula / yönet:\*\* Telefon + onay kodu ile randevu görüntüleme, iptal ve erteleme.

\- \*\*Yasal sayfalar:\*\* KVKK aydınlatma metni, gizlilik politikası, çerez politikası, açık rıza metni, kullanım koşulları, iptal ve gelmeme politikası. Metinleri Türkçe ve makul bir taslak olarak yaz; her sayfanın başında "Bu metin bilgilendirme amaçlıdır, hukuki danışmanlık yerine geçmez" notu olsun.

\- \*\*404 ve 500 sayfaları\*\*, bakım modu.



\*\*SEO ve performans:\*\*

\- Her sayfada benzersiz `title`, `meta description`, Open Graph ve Twitter kartı.

\- `LocalBusiness` ve `HairSalon` (veya uygun tip) için JSON-LD, hizmetler için `Service` şeması.

\- `sitemap.xml` ve `robots.txt`.

\- Kanonik URL'ler, Türkçe URL'ler (ör. `/hizmetler`, `/randevu`).

\- Core Web Vitals hedefi: LCP < 2.5s, CLS < 0.1, INP < 200ms.



\---



\## 7. RANDEVU AKIŞI (ÇEKİRDEK ÖZELLİK)



Akış mobil öncelikli, 4 adımlı, her adımda ilerleme göstergesi ve sağ/üst köşede özet kartı olsun:



1\. \*\*Hizmet seçimi:\*\* Birden fazla hizmet seçilebilsin. Toplam süre ve fiyat anlık hesaplansın.

2\. \*\*Berber seçimi:\*\* Belirli bir berber veya "Fark etmez" (sistem uygun berberi otomatik atar; en az yoğun olanı tercih etsin).

3\. \*\*Tarih ve saat:\*\* Yalnızca gerçekten müsait slotlar gösterilsin. Slot hesabı şunları dikkate almalı: berberin çalışma saatleri, molalar, izinler, mevcut randevular, hizmet süresi, varsa hazırlık/temizlik tamponu, minimum ön rezervasyon süresi, maksimum ileri tarih penceresi, geçmiş saatler. Gün seçici takvim olsun.

4\. \*\*Bilgiler ve onay:\*\* Ad soyad, telefon (zorunlu, Türkiye formatı doğrulaması), opsiyonel e-posta, müşteri notu, KVKK onay kutusu (zorunlu, kayıt altına alınır). Gönder butonu çift tıklamaya karşı korunsun.



\*\*Sonuç ekranı:\*\* Onay kodu büyük ve net gösterilsin, randevu özeti, "Takvime ekle" (.ics indirme), "Randevumu yönet" bağlantısı.



\*\*Kritik teknik gereksinimler:\*\*

\- \*\*Çakışma koruması:\*\* Randevu oluşturma tek bir transaction içinde yapılsın; EXCLUDE kısıtı ihlali gelirse kullanıcıya "Bu saat az önce doldu, lütfen başka bir saat seçin" mesajı gösterilsin ve slotlar yenilensin.

\- \*\*Idempotency:\*\* Form her gönderimde bir `idempotency\_key` üretsin; aynı anahtar ile tekrar gelen istek ikinci randevu oluşturmasın.

\- \*\*Slot API'si:\*\* Müsaitlik hesabı sunucuda yapılsın, istemci tarafında kopyalanmasın. Önbellekleme kısa süreli olsun ve randevu oluşturulunca geçersiz kılınsın.

\- \*\*Zaman dilimi:\*\* Sunucu ve veritabanı UTC, kullanıcı arayüzü Europe/Istanbul.

\- \*\*Doğrulama:\*\* Telefon numarasını normalize et (+90 formatı). Geçmişe randevu alınamaz.

\- \*\*Hata durumları:\*\* Kapalı gün, dolu gün, bakım modu, sistem hatası için anlaşılır Türkçe mesajlar.



\*\*Müşteri yönetimi (hesapsız):\*\*

\- Randevu onay kodu + telefon ile randevu sorgulama.

\- İptal: Politikadaki süre (ör. X saat) öncesinde mümkün. Süre geçtiyse iptal edilemez, bilgilendirme gösterilir.

\- Erteleme: Yeni slot seçimi, aynı kurallar geçerli. Eski randevu, yeni randevu oluşmadan iptal edilmesin (transaction ile).



\---



\## 8. UYGULAMA İÇİ BİLDİRİMLER (SMS/E-POSTA YOK)



\- \*\*Yönetim paneli:\*\* Üst menüde bildirim zili, okunmamış sayısı. Olaylar: yeni randevu, iptal, erteleme, yeni yorum (onay bekleyen), yaklaşan randevu (örn. 30 dakika kala).

\- \*\*Müşteri tarafı:\*\* Randevu sorgulama sayfasında randevu durumu değişikliklerini gösteren bir alan (ör. "Berberiniz randevunuzu güncelledi"). Müşteri için ayrıca bir bildirim tablosu zorunlu değil; sorgulama sayfası yeterli.

\- \*\*Teknik:\*\* Realtime için Supabase Realtime tercih et; desteklenmiyorsa kısa aralıklı polling (ör. 30 sn) ile yap. Bildirim üretimi veritabanı trigger'ı veya uygulama katmanındaki service ile olsun; hangisini seçtiğini gerekçelendir.

\- Bildirimler tek tıkla okundu olarak işaretlenebilsin, toplu okundu seçeneği olsun.



\---



\## 9. YÖNETİM PANELİ



\*\*Erişim:\*\* Supabase Auth ile e-posta + şifre girişi. Admin ve staff rolleri. Staff yalnızca kendi takvimini ve randevularını görür, hizmet/fiyat/ayar değiştiremez. Admin her şeyi yönetir. Şifre sıfırlama akışı olsun.



\*\*Modüller:\*\*

\- \*\*Takvim:\*\* Gün ve hafta görünümü, berber sütunları, randevu kartları (durum renkleriyle). Randevuya tıklayınca detay çekmecesi (drawer).

\- \*\*Randevu yönetimi:\*\* Manuel randevu ekleme (telefonla gelen müşteri), düzenleme, iptal, "Geldi / Gelmedi / Tamamlandı" işaretleme, dahili not.

\- \*\*Hizmetler:\*\* Oluştur, düzenle, sırala, pasife al. Fiyat değişikliği geçmiş randevuları etkilemez.

\- \*\*Berberler:\*\* Profil, fotoğraf yükleme, hizmet yetkinlikleri, haftalık çalışma saatleri ve molalar, izin ekleme.

\- \*\*Müşteriler:\*\* Liste, arama (ad/telefon), randevu geçmişi, dahili notlar. KVKK için müşteri verisi silme talebi işlemi (anonimleştirme veya silme; ne yaptığını README'de yaz).

\- \*\*Galeri:\*\* Yükleme, kategori, sıralama, yayın durumu. Görseller sunucuda WebP'ye dönüştürülsün ve boyut sınırı olsun.

\- \*\*Yorumlar:\*\* Onay bekleyenler, onayla / gizle / sil. Yorum yalnızca tamamlanmış randevuya bağlı ve doğrulanmış kişiler tarafından yazılabilsin (onay kodu ile).

\- \*\*Duyurular:\*\* Başlık, metin, yayın tarih aralığı, aktif/pasif.

\- \*\*Ayarlar:\*\* İşletme bilgileri, adres, harita koordinatı, iptal politikası süresi, randevu ön süresi, ileri tarih penceresi, KVKK metin versiyonu.

\- \*\*Raporlar (temel):\*\* Seçilen tarih aralığında randevu sayısı, tamamlanan/iptal/gelmeyen oranları, en çok tercih edilen hizmetler, berber bazlı doluluk, tahmini gelir (fiyat × tamamlanan randevu, ödeme olmadığı için "tahmini" olarak etiketlensin). Basit grafikler (Chart.js veya inline SVG).

\- \*\*Denetim kaydı:\*\* Kritik değişikliklerin listesi, filtrelenebilir.



\---



\## 10. GÜVENLİK VE KVKK



\- Tüm form ve işlemlerde \*\*CSRF token\*\* koruması.

\- Tüm SQL sorgularında \*\*prepared statement\*\*. Ham kullanıcı girdisi asla sorguya eklenmeyecek.

\- Çıktıda \*\*XSS koruması\*\* (şablonlarda varsayılan escape, güvenli HTML sanitize gerekiyorsa kütüphane).

\- \*\*Rate limiting:\*\* Randevu oluşturma, giriş ve onay kodu denemelerinde (brute force önleme).

\- \*\*Bot koruması:\*\* Cloudflare Turnstile veya eşdeğeri (opsiyonel, `.env` ile açılıp kapanabilsin), honeypot alanı.

\- \*\*Güvenlik başlıkları:\*\* CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS (HTTPS ortamında).

\- \*\*Oturum:\*\* Güvenli, HttpOnly, SameSite çerezler.

\- \*\*Sırlar:\*\* Tüm anahtarlar `.env` içinde. `.env` asla commit edilmeyecek. `.env.example` tüm değişkenleri açıklamalı.

\- \*\*Onay kodu:\*\* Yeterince uzun ve rastgele (kriptografik olarak güvenli), tahmin edilemez.

\- \*\*KVKK:\*\* Aydınlatma metni gösterimi, açık rıza kaydı (metin versiyonu ve zaman damgası ile), veri minimizasyonu (yalnızca gerekli alanlar), veri silme/erişim talebi için süreç, veri saklama süresi tanımı, loglarda kişisel veri maskeleme.



\---



\## 11. TASARIM YÖNERGESİ



\- \*\*Duygu:\*\* Güven veren, sıcak, premium ama gösterişsiz. Berber kültürüne saygılı; klişe "retro barber" kitschinden kaçın.

\- \*\*Mobil öncelikli:\*\* Önce 375px genişliğini tasarla, sonra masaüstüne genişlet.

\- \*\*Dark ve light tema:\*\* Varsayılan tema sen seç, kullanıcı tercihi (sistem teması) desteklensin.

\- \*\*Bileşenler:\*\* Buton durumları (hover, focus-visible, disabled, loading), form hata ve başarı durumları, boş durumlar (ör. "Bu gün için müsait saat yok"), iskelet (skeleton) yüklemeler, toast bildirimleri.

\- \*\*Erişilebilirlik:\*\* WCAG 2.1 AA kontrast, klavye ile tam gezinme, anlamlı `aria-\*` etiketleri, `prefers-reduced-motion` desteği, form etiketleri.

\- \*\*Performans:\*\* Görseller WebP/AVIF, lazy loading, kritik CSS, minimum JavaScript.

\- \*\*Tutarlılık:\*\* Tek bir tasarım token seti (renk, boşluk, radius, gölge, tipografi ölçeği). Tüm sayfalar bunu kullanır.



\---



\## 12. KLASÖR YAPISI (ÖNERİ, GEREKİRSE DEĞİŞTİR)



```

berber-randevu/

├── public/              # Web root (index.php, assets, derlenmiş CSS/JS)

├── src/                 # Uygulama kodu (Controllers, Services, Repositories, Domain)

├── templates/           # PHP şablonları

├── config/              # Yapılandırma (env okuma)

├── supabase/

│   ├── migrations/      # Sıralı SQL migration'ları

│   └── seed.sql

├── tests/               # PHPUnit ve Playwright

├── docs/                # AGENTS\_USED.md, mimari notlar, KVKK süreçleri

├── scripts/             # Yardımcı betikler

├── .github/workflows/   # CI

├── LICENSE

├── README.md

└── ...

```



\---



\## 13. README İÇERİĞİ (ZORUNLU)



README Türkçe olacak ve şunları içerecek:

1\. Proje tanıtımı ve ekran görüntüleri (placeholder olabilir)

2\. Özellik listesi

3\. Teknoloji yığını ve mimari özeti

4\. \*\*Adım adım kurulum:\*\* PHP ve Composer kurulumu, Supabase projesi oluşturma, migration'ların çalıştırılması, seed verisinin yüklenmesi, `.env` doldurma, yerel çalıştırma (`php -S` veya belgelenen yöntem), testleri çalıştırma

5\. \*\*Supabase RLS politikalarının özeti\*\*

6\. \*\*Deploy rehberi\*\* (paylaşımlı PHP hosting ve VPS için ayrı kısa notlar)

7\. Ortam değişkenleri tablosu

8\. Kullanılan agency-agents skill'lerinin özeti (`docs/AGENTS\_USED.md` linki)

9\. Katkı sağlama (`CONTRIBUTING.md` linki), güvenlik açığı bildirimi (`SECURITY.md` linki)

10\. Lisans: MIT



\---



\## 14. ÇALIŞMA SIRASI



Bu sırayla ilerle ve her aşamayı ayrı commit'lerle kaydet:



1\. \*\*Plan:\*\* Kısa bir mimari plan ve şema taslağı yaz (`docs/ARCHITECTURE.md`). Bitirince devam et, beklemeden.

2\. \*\*Repo kurulumu:\*\* GitHub repo, LICENSE, .gitignore, temel dokümanlar.

3\. \*\*Tasarım sistemi:\*\* Marka adı, logo (SVG), renk ve tipografi token'ları, temel bileşenler.

4\. \*\*Veritabanı:\*\* Migration'lar, RLS, EXCLUDE kısıtı, seed verisi.

5\. \*\*Çekirdek backend:\*\* Konfigürasyon, Supabase bağlantısı, slot motoru, randevu oluşturma (transaction + idempotency).

6\. \*\*Randevu akışı:\*\* 4 adım UI, slot API, sonuç ekranı, .ics, randevu sorgulama/iptal/erteleme.

7\. \*\*Public sayfalar:\*\* Ana sayfa, hizmetler, ekip, galeri, yorumlar, iletişim, yasal sayfalar, SEO.

8\. \*\*Yönetim paneli:\*\* Auth, takvim, randevu yönetimi, hizmet/berber/müşteri, galeri, yorum, duyuru, ayarlar, raporlar, denetim kaydı.

9\. \*\*Uygulama içi bildirimler.\*\*

10\. \*\*Güvenlik ve KVKK sertleştirmesi.\*\*

11\. \*\*Testler:\*\* Slot motoru birim testleri (sınır durumları: gece yarısı geçişi, mola çakışması, izin, DST yok ama tampon süreleri), çakışma testi (eşzamanlı istekler), Playwright ile randevu akışı E2E.

12\. \*\*CI, dokümantasyon, CHANGELOG, v0.1.0 etiketi.\*\*



\---



\## 15. KABUL KRİTERLERİ (BİTTİ SAYILMASI İÇİN)



\- \[ ] Repo GitHub'da public, MIT lisanslı, yalnızca `main` branch'i var.

\- \[ ] Yeni bir geliştirici README'yi takip ederek 30 dakika içinde projeyi yerelde çalıştırabiliyor.

\- \[ ] İki farklı istemci aynı slotu aynı anda rezerve etmeye çalıştığında yalnızca biri başarılı oluyor (test ile kanıtlanmış).

\- \[ ] Müsaitlik hesabı mola, izin, mevcut randevu ve hizmet süresini doğru hesaplıyor (birim testleri geçiyor).

\- \[ ] Hiçbir sayfada `service\_role` anahtarı veya gizli bilgi görünmüyor.

\- \[ ] Tüm tablolarda RLS aktif ve politikalar belgelenmiş.

\- \[ ] Randevu akışı mobilde sorunsuz çalışıyor (Playwright ile doğrulanmış).

\- \[ ] Lighthouse mobil performans ve erişilebilirlik skoru 90+ (ana sayfa ve randevu sayfası).

\- \[ ] KVKK metinleri ve açık rıza kaydı çalışıyor.

\- \[ ] CI tüm adımlarda yeşil.

\- \[ ] `docs/AGENTS\_USED.md` kullanılan skill'leri listeliyor.



\---



\## ÖNEMLİ KURALLAR



\- Ödeme, SMS, WhatsApp veya e-posta gönderimi ekleme.

\- Tek şube varsayımını koru; çoklu şube mantığı kurma (ama veri modelini gelecekte şubeye genişletilebilir tut: `branch\_id` yerine şimdilik tek işletme kaydı yeterli, gerekçeyi README'ye yaz).

\- Gereksiz bağımlılık ekleme. Her paketi eklemeden önce neden gerektiğini belgele.

\- Hiçbir sırrı (token, anahtar, şifre) kodda, commit'te veya log'da bırakma.

\- Her tamamlanan aşamada kısa bir özet ver: ne yapıldı, hangi kararlar alındı, sıradaki adım ne.

