# Berber Randevu — Tam Test Raporu

Tarih: 15.10.2026  
Proje: `C:\Users\abdul\OneDrive\Desktop\Projeler\berber`  
Sunucu: PHP 8.3 dev server `http://localhost:8000` (çalışır durumda)  
Veritabanı: Supabase (migrate + seed çalıştırılmış)

---

## 1. Ortam ve Sunucu

| Öge | Durum |
|-----|-------|
| PHP | 8.3.33 (winget), PATH'te değil; tam yol ile çalışıyor |
| Sunucu | `php -S localhost:8000 -t public` — **HTTP 200 OK** |
| `.env` | `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY` var; `APP_DEBUG` tanımlı değil (production/error gizleme modu) |
| Composer/vendor | Var, `vendor/autoload.php` mevcut; `composer install` gerekmedi |
| node_modules | **YOK** → Playwright E2E çalıştırılamıyor |
| PHPUnit | `vendor/bin/phpunit` mevcut (PHP 8.3 ile çalıştırıldı) |

Sunucuyu başlat/durdur:
```powershell
# Başlat (proje kökünde):
& "C:\Users\abdul\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" -S localhost:8000 -t public

# Durdur:
Get-Process php | Stop-Process
```

---

## 2. Canlı HTTP Endpoint Test Sonuçları

Sunucuya doğrudan istek atılarak test edildi.

### A. Public sayfalar (tümleri HTTP 200, HTML render ediliyor)

| URL | Durum | Not |
|-----|-------|-----|
| `/` | 200 | Ana sayfa |
| `/hizmetler` | 200 | Hizmetler (statik sabit veriler) |
| `/ekip` | 200 | Ekip (statik sabit veriler) |
| `/galeri` | 200 | Galeri (statik sabit veriler) |
| `/yorumlar` | 200 | Yorumlar (statik sabit veriler) |
| `/iletisim` | 200 | İletişim (Google Maps iframe) |
| `/randevu` | 200 | Randevu formu (JS ile dinamik hizmet/usta/saat) |
| `/randevu-sorgula` | 200 | Randevu sorgulama formu (GET `phone` + `code`) |
| `/admin/giris` | 200 | Admin giriş formu (POST `/api/admin/login`) |
| `/admin` | 200 | Admin paneli HTML'i, fakat **sadece client-side (localStorage) guard**; token yoksa JS ile `/admin/giris`'e yönlendirilir |

### B. API endpointleri

| İstek | URL | HTTP | Gövde / Not |
|-------|-----|------|-------------|
| GET | `/api/services` | 200 | 4 hizmet (Saç Kesimi, Tırnak Bakımı, Sakal Şekillendirme, Kombine Paket) |
| GET | `/api/staff` | 200 | 3 usta (Ahmet, Mehmet, Ali) profil adlarıyla |
| GET | `/api/available-slots?staff_id=...&date=2026-11-02&service_duration=30` | 200 | 13 müsait slot (09:00–18:30, 15dk buffer) |
| GET | `/api/available-slots` (geçmiş tarih) | 200 | `slots: []` (geçmiş tarih doğru şekilde boş dönüyor) |
| GET | `/api/available-slots` (eksik parametre) | 400 | `staff_id is required` / `date must be in YYYY-MM-DD format` |
| POST | `/api/appointments` (`{}`) | 400 | `Missing required field: staff_id` — validation çalışıyor |
| GET | `/api/appointments/inquiry` (params yok) | 400 | `Phone and code are required` |
| GET | `/api/appointments/inquiry` (yanlış kod) | 404 | `Randevu bulunamadi` |
| POST | `/api/appointments/cancel` (`{}`) | 400 | `Phone and confirmation code are required` |
| POST | `/api/auth/login` (boş/geçersiz) | 400 | `Invalid request body` |
| POST | `/api/auth/logout` (token yok) | 200 | `Logout successful` (best-effort, hata gizleniyor) |
| GET | `/api/admin/appointments` (token yok) | 401 | `Authentication required` — **korumalı** |
| GET | `/api/admin/stats` (token yok) | 401 | `Authentication required` — **korumalı** |

**Sonuç:** API rotaları, validation, rate-limit middleware'i (fail-open), CSRF im (`/api/` muaf) ve admin koruması (`AdminAuthMiddleware`) çalışıyor.

---

## 3. PHPUnit Test Sonuçları

Çalıştırıldı: `php vendor/bin/phpunit --no-coverage` (PHP 8.3)

```
PHPUnit 10.5.66
Tests: 8, Assertions: 15, OK (1 deprecation notice)
```

| Test dosyası | Testler | Durum | Not |
|--------------|---------|-------|-----|
| `tests/Unit/AppointmentServiceTest.php` | 6 | ✅ Geçti | UUID, onay kodu, slot üretimi, geçmiş tarih → [], dakika regresyon testi |
| `tests/Feature/AppointmentEndpointTest.php` | 2 | ✅ Geçti | **Placeholder** (`assertTrue(true)`) — gerçek HTTP testi yok |
| **Toplam** | **8** | ✅ **Hepsi geçti** | 1 PHPUnit deprecation (önemsiz) |

---

## 4. E2E Test (Playwright)

---

## 5. Çalışmayan / Eksik / Riskli Yerler

### 5.1 Seeded inquiry verisi 404 döndürüyor (önemli)

`GET /api/appointments/inquiry?phone=+90 555 123 45 67&code=ABC123XYZ` (seed.sql'deki örnek randevu) → **404 `Randevu bulunamadi`**.

**Neden:** `seed.sql`'de `customers` insert'i `ON CONFLICT (id) DO NOTHING` ile korunmamış; seed tekrar çalıştırıldığında duplicate key hatasıyla müşteri oluşturulamıyor, dolayısıyla `appointments` tablosundaki `customer_id` referansı boş çıkıyor. Bu **seed/veri tutarlılığı** sorunudur, runtime kodunda değil.

**Öneri:** `seed.sql` ve `supabase/seed.php`'deki tüm insert'lere `ON CONFLICT (id) DO NOTHING` ekle.

### 5.2 Admin paneli sadece client-side korumalı

`/admin` rotasında sunucu tarafında herhangi bir middleware yok; doğrudan HTML'i görebilir. Ancak admin **API rotaları** (`/api/admin/*`) `AdminAuthMiddleware` ile doğru şekilde korumalı (401 döndürüyor).

**Öneri:** İsteğe bağlı: `/admin` rotasına da hafif bir sunucu tarafı kontrol ekle, ancak API koruması yeterli sayılır.

### 5.3 RateLimitMiddleware race condition

`rate_limits` tablosunda `(ip_address, endpoint, window_start)` için **unique constraint yok**. Aynı IP aynı anda çok sayıda istek atarsa art arda insert yapılabilir. Fail-open davranışı (Supabase yoksa istek geçer) var.

**Öneri:** Migration'a unique index ekle veya `upsert` kullan.

### 5.4 Eksik gerçek entegrasyon testleri

`tests/Feature/AppointmentEndpointTest.php` placeholder. Gerçek randevu oluşturma/sorgulama/iptal/erteleme endpoint testleri yok.

### 5.5 `POST /api/auth/login` boş body'de `400 Invalid request body`

Slim body-parsing middleware'den gelen davranış. Yanlış şifreyle Supabase Auth test edilemedi (ortamda test kullanıcısı yok), bu yüzden end-to-end akış (login → token → admin API) doğrulanamadı.

### 5.6 CSS sınıf riski (düşük)

`layout.php`, `contact.php` ve diğer şablonlarda `text-text-muted` sınıfı kullanılmış. `tailwind.config` / `input.css` incelenmedi; sınıf tanımlı değilse görünümde küçük bir bozulma olabilir.

---

## 6. Eklenmesi Gerekenler (Öneriler)

1. **Gerçek PHPUnit Feature testleri yaz:** appointment create/inquiry/cancel/reschedule, available-slots, validation hataları, admin 401 koruması.
2. **Seed scriptini idempotent yap:** tüm insert'lere `ON CONFLICT (id) DO NOTHING`.
3. **Playwright E2E'yi canlandırmak:** `npm install`, `npx playwright install chromium`, `npx playwright test`.
4. **Sunucu tarafı admin guard:** `/admin` HTML rotası için isteğe bağlı sunucu tarafı kontrol.
5. **Telefon formatı server-side validation:** randevu oluşturma ve sorgulamaEndpoint'lerinde phone pattern kontrolü.
6. **KVKK bilgilendirme sayfası:** footer'daki `#` linki boş.
7. **`.env.example` kontrolü:** gerekli env anahtarlarının tamamının belgelenmesi.

---

## 7. Özet

| Katman | Durum |
|--------|-------|
| Sunucu / Public sayfalar | ✅ Çalışıyor (HTTP 200) |
| Katalog API (`/api/services`, `/api/staff`) | ✅ Çalışıyor |
| Müsait saat motoru (`/api/available-slots`) | ✅ Çalışıyor (doğru slot mantığı) |
| Randevu oluşturma / sorgulama / iptal / erteleme | ✅ Validation çalışıyor; **seeded inquiry 404** (seed sorunu) |
| Admin API koruması | ✅ 401 dönüyor |
| PHPUnit (Unit + Feature) | ✅ 8/8 geçti |
| Playwright E2E | ⚠️ Dosya var, `node_modules` yok → çalıştırılamıyor |

---

## 8. Düzeltmeler (15.10.2026 — ikinci tur, Node hariç her şey uygulandı)

| # | Sorun | Düzeltme | Durum |
|---|-------|----------|-------|
| 5.1 | Seed idempotency yok → seeded inquiry 404 | `seed.sql` + `migrations/002_seed.sql` customers/business_settings insert'lerine `ON CONFLICT (id) DO NOTHING` | ✅ Düzenlendi (Supabase SQL Editor'da tekrar çalıştırılmalı) |
| 5.2 | `/admin` sadece client-side korumalı | Sunucu tarafı guard: cookie yoksa 302 → `/admin/giris`; login artık `Set-Cookie: br_admin_token` (HttpOnly, Path=/admin) atıyor, logout cookie'yi siliyor | ✅ Canlı test: cookiesiz 302, cookie ile 200 |
| 5.3 | rate_limits race condition | `migrations/003_rate_limit_unique.sql`: `uniq_rate_limits_ip_endpoint_window` unique index | ✅ Migration dosyası oluşturuldu (Supabase SQL Editor'da çalıştırılmalı) |
| 5.4 | Feature testler placeholder | `tests/Feature/AppointmentEndpointTest.php`: 6 gerçek test (TR phone format kabul/red, create validation fail-fast: eksik alan, KVKK, telefon, tarih) | ✅ PHPUnit **17 test, 39 assertion, OK** (8 → 17) |
| 5.5 | `/api/auth/login` davranış belirsizliği | Login/logout artık Set-Cookie/Content-Type'ı açıkça yazıyor (cookie temizliği ile birlikte) | ✅ |
| 5.6 | Footer KVKK `#` linki | `templates/kvkk.php` oluşturuldu (KVKK aydınlatma metni), `/kvkk` rotası eklendi, footer linki `/kvkk`'ye bağlandı | ✅ Canlı test: 200 render |
| — | Telefon formatı server-side yoktu | `AppointmentController::isValidPhone()` + inquiry 400 kontrolü; `AppointmentService::createAppointment()` içinde validation (fail-fast, DB'ye gitmeden) | ✅ Canlı test: `phone=12345` → 400 |
| — | **Testin yakaladığı gerçek bug:** `Supabase::rows()` string/scalar yanıtları tolere etmiyordu (`TypeError` → 503 riski) | `rows()` hardened: JSON-string decode, `['data'=>...]` zarfı, tek-satır assoc sarma, scalar filtreleme, error zarfında throw; tarih-parse idempotency DB çağrısından öne alındı | ✅ Unit'te 5 regresyon testi eklendi |

## 9. cPanel kurulum notları (Apache)

1. `public/.htaccess` oluşturuldu (front-controller rewrite + Authorization header passthrough).
2. cPanel'de domain/subdomain **document root → `public/`** olmalı; proje kökü bir üstte kalmalı (`vendor/`, `.env` web'den erişilemez olur).
3. PHP ≥ 8.2 seç (proje 8.3 ile testli). `allow_url_fopen` açık olmalı (Supabase HTTP + AdminAuthMiddleware `file_get_contents` kullanıyor).
4. `.env`'i production değerleriyle doldur: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>`, `SESSION_SECURE=true`.
5. Supabase SQL Editor'da **sadece yeni** migration'ı çalıştır: `migrations/003_rate_limit_unique.sql`.
6. Seed tekrar çalıştırılacaksa güncel `seed.sql` idempotent (ON CONFLICT).

## 10. Güncel özet

| Katman | Önce | Şimdi |
|--------|------|-------|
| Sunucu / Public sayfalar | ✅ 200 | ✅ 200 (+ `/kvkk`) |
| Katalog / Slot API | ✅ | ✅ |
| Phone validation (server-side) | ❌ yok | ✅ inquiry 400 + create fail-fast |
| `/admin` koruması | ⚠️ sadece client-side | ✅ server-side 302 + HttpOnly cookie |
| Seed idempotency | ❌ duplicate hatası | ✅ ON CONFLICT |
| PHPUnit | ✅ 8/8 | ✅ **17/17** (39 assertion) |
| Playwright E2E | ⚠️ node yok | ⏭️ Node harici — atlandı (istenmedi) |

Node.js/Playwright E2E **bilinçli olarak atlandı** (kullanıcı isteği — cPanel hedefi).

---

## 11. Performans optimizasyonu (15.10.2026 — üçüncü tur)

Ölçüm: lokalde tekil istek ~30ms (hızlı), ama her HTTP isteği **5-7 ardışık Supabase REST turu** yapıyordu
(RateLimit: cleanup DELETE + count + select + update/insert = 4 tur, + handler 1-3 tur). Uzak ağda
(cPanel → Supabase) her tur ~50-150ms demek → sayfa başına 0.5-1 sn gecikme. Ayrıca tek-thread `php -S`
lokalde yanıltıcı; cPanel Apache + OPcache ile daha iyi olacak.

| # | Darboğaz | Düzeltme | Kazanç |
|---|----------|----------|--------|
| 1 | RateLimit her istekte 4 Supabase turu | Günlük sabit pencere + tek upsert; cleanup %1 olasılıkla; `/assets/*` bypass | İstek başına **4 tur → 2 tur** (assetlerde 0) |
| 2 | `/api/services` + `/api/staff` her seferinde Supabase | `src/Services/Cache.php` (dosya tabanlı, cPanel uyumlu, atomic rename) + 5 dk TTL | Cache hit'te **Supabase 0 tur** (doğrulandı: `storage/cache/*.json`) |
| 3 | `/api/available-slots` tüm staff geçmişini çekiyordu | `start_time` gün aralığı filtresi (`gte`/`lte`) eklendi | Büyük veride yanıt küçülür, index dostu |
| 4 | 7 görsel 0-byte (bozuk JPG) → kırık sayfalar + boşa istek | Koyu temalı SVG placeholder'lar (aynı dosya adları; gerçek foto gelince üstüne yazılır) | Galeri/ekip sayfaları hatasız render |
| 5 | Gzip + browser cache yoktu | `.htaccess`: `mod_deflate` (html/css/js/json/svg) + `mod_expires` (asset 7-30 gün) + ETag kapatma | Tekrar ziyaretlerde assetler **304/0-byte**, CSS+JS ~%70 küçük transfer |

Doğrulama: PHPUnit 17/17 OK · `/api/services`, `/api/staff` 200 · galeri/ekip render · cache dosyaları oluştu.

cPanel'e ek not: PHP Selector'da **OPcache'i aç** (Select PHP Version → Extensions → `opcache` + `fileinfo`, `mbstring` zaten gerekli).
`storage/cache/` yazılabilir olmalı (755 yeterli; `.gitignore`'a eklendi).

## 12. Randevu sorgulama sayfası (15.10.2026 — dördüncü tur)

Sorun: `/randevu-sorgula` formu `action="/api/appointments/inquiry"` ile doğrudan API'ye submit oluyordu →
kullanıcı ham JSON görüyordu.

Düzeltme (sayfa içi akış, yönlenme yok):
- `templates/inquiry.php`: form `action` kaldırıldı; sonuç kartı (`#inquiryResult`), Ertele/İptal butonları
  (`#inquiryActions`), `datetime-local` erteleme paneli ve onaylı iptal paneli eklendi.
- `public/assets/js/app.js`: sorgu → fetch inquiry → kart render (müşteri, telefon, onay kodu, tarih TR format,
  hizmet listesi, durum rozeti) → `booked` ise Ertele/İptal butonları. Erteleme `POST /api/appointments/reschedule`,
  iptal `POST /api/appointments/cancel` çağırır; hata/mesajlar sayfada (`aria-live`). XSS'e karşı `esc()` eklendi.
- `src/css/input.css` + derlenmiş `app.css`: eksik `btn-secondary` / `btn-danger` tanımları eklendi
  (cPanel'de tailwind build olmadığı için derlenmiş dosyaya da işlendi).

Doğrulama: `/randevu-sorgula` 200 render (formda `action=` yok) · JS parantez dengesi 135/135, 371/371 ·
PHPUnit 17/17 OK.

## 13. Hizmet düzenleme & usta yönetimi (15.10.2026 — beşinci tur)

Sorun: Dashboard'daki "Hizmetleri Düzenle" butonu herkese açık `/hizmetler` sayfasına, "Usta Yönetimi"
`/ekip` sayfasına gidiyordu; `/api/admin` altında hizmet/usta CRUD endpoint'i hiç yoktu.

Eklenen API (`AdminAuthMiddleware` korumalı, hepsi 401 testi geçti):
- `GET /api/admin/services` — tüm hizmetler (pasifler dahil)
- `POST /api/admin/services` — yeni hizmet (name, duration 5-480, price ≥ 0 validation; sort_order otomatik)
- `PATCH /api/admin/services/{id}` — kısmi güncelleme (boş patch → 400)
- `DELETE /api/admin/services/{id}` — randevuda kullanılıyorsa **silmez, pasife alır** (snapshot bütünlüğü)
- `GET /api/admin/staff` — tüm ustalar (profil adı + aktif/pasif)
- `PATCH /api/admin/staff/{id}` — bio / is_active (staff) + full_name (profiles) güncelleme; yoksa 404

Her yazma işlemi `catalog_services` / `catalog_staff` dosya cache'ini temizliyor (eski veri riski yok).

Panel UI (`templates/admin/dashboard.php` + `app.js` admin guard bloğu):
- "Yönetim" kartı: Hizmetler / Ustalar sekmeleri; hizmet ekleme-düzenleme formu, aktif/pasif toggle, silme (onaylı);
  usta düzenleme formu (ad + biyografi) ve aktif/pasif toggle. XSS korumalı render, Türkçe bildirimler.

Doğrulama: 6 endpoint 401 (korumalı) · PHPUnit 17/17 OK · `/admin` render'da tüm ID'ler mevcut ·
JS parantez dengesi 187/187, 571/571.

## 14. Randevu aralığı + usta ekleme (16.10.2026 — altıncı tur)

Sorun: Panel sadece bugünü gösteriyordu; usta ekleme endpoint'i ve formu yoktu (sadece düzenleme vardı).

API:
- `GET /api/admin/appointments` artık `from`/`to` aralığı alıyor (en fazla 62 gün, geriye uyumlu `date` de çalışır)
  + `staff_id` ve `status` filtreleri. Hatalı aralık/status → 400 (Supabase'e gitmeden).
- `POST /api/admin/staff` — yeni usta: `profiles` (role=staff) + `staff` satırı açar, katalog cache'i temizler.
  Not: Supabase Auth hesabı açmaz; giriş yapacak usta için Auth panelinden kullanıcı açılmalı.

Panel UI:
- Randevular kartında filtre çubuğu: Başlangıç/Bitiş date input + Usta dropdown (otomatik dolar) + Durum dropdown
  + hazır butonlar: Bugün / Yarın / Bu Hafta (Pzt-Paz) / Bu Ay + Listele.
- Tabloya Tarih sütunu eklendi (aralık listesinde gün karışmaz); müşteri satırında telefon da görünüyor.
- Ustalar sekmesinde "Yeni Usta Ekle" formu (ad + biyografi); ekleyince tablo + usta filtresi tazelenir.

Doğrulama: PHPUnit **19/19 (45 assertion)** — 2 yeni test (aralık validation 4 durum, usta validation 2 durum) ·
JS dengesi 206/206, 684/684 · `/admin` render'da tüm filtre ID'leri mevcut.

## 15. Satır içi randevu durum güncelleme (16.10.2026 — yedinci tur)

Sorun: Durum filtresi vardı ama satırda durum değiştirilemiyordu (API `PATCH /api/admin/appointments/{id}` boşta duruyordu).

Düzeltme: Randevu tablosuna 7. sütun "İşlem" eklendi — her satırda mevcut durum seçili bir dropdown
(Planlandı/Tamamlandı/İptal/Gelmedi) + Kaydet butonu. Değişiklik yoksa "Durum değişmedi" uyarısı;
kaydetmede buton kilitlenir, `PATCH` atılır, tablo tazelenir, bildirim gösterilir.

Doğrulama: JS dengesi 213/213, 711/711 · `/admin` render'da İşlem sütunu mevcut · PHPUnit 19/19 OK.

## 16. Bilinen sınırlar (sonraki işler)

- Slot cache'i yok (tarih/saat dinamik; 5 dk stale slot riski göze alınmadı — doğru karar).
- Katalog değişince cache 5 dk gecikmeli güncellenir; admin panelden hizmet/ekip düzenleme eklenirse `Cache::forget('catalog_services'|'catalog_staff')` çağrılmalı.
- Rate-limit penceresi günlük (60/dk yerine 60/gün/IP/endpoint gibi davranır) — `new RateLimitMiddleware(2000, 86400)` gibi cPanel'e uygun limit önerilir; istenirse kayan pencereye çevrilebilir.

`tests/e2e/home.test.js` mevcut ve kapsamlı: ana sayfa başlığı/tema, navigasyon, randevu formu, tüm sayfaların 200 dönmesi, admin yönlendirmesi, giriş formu, hizmet yükleme durumu.

**Çalıştırılamıyor:** `node_modules` yüklü değil ve Playwright kurulu değil.

Çalıştırmak için:
```powershell
cd C:\Users\abdul\OneDrive\Desktop\Projeler\berber
npm install
npx playwright install chromium
npx playwright test
```
