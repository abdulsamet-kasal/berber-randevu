# Mimari Plan ve Veritabanı Şema Taslağı

## Genel Mimari

- **Backend**: PHP 8.2+ ile Slim Framework kullanımı hafif ve etkili.
- **Veritabanı**: Supabase (PostgreSQL) ile PostgREST ve PDO üzerinden erişim.
- **Frontend**: Sunucu taraflı render edilmiş PHP şablonları, Alpine.js ile hafif etkileşim, Tailwind CSS (derlenmiş) stil.
- **Kimlik**: Supabase Auth (e-posta/şifre) ve Rol Tabanlı Erişim Kontrolü (RBAC) ile admin/staff/anonymous.
- **Realtime**: Supabase Realtime ile uygulama içi bildirimler.
- **Önbellekleme**: Slot API sonuçları için kısa süreli (ör. 30 sn) önbellek.
- **CI/CD**: GitHub Actions (lint, statik analiz, testler, üretim dağıtımı).

## Veritabanı Şema Özeti

### Tablolar

1. **profiles**: auth.users ile 1:1 ilişki, rol (admin|staff), görünen ad.
2. **staff**: berberler, fotoğraf, biyografi, aktiflik, user_id (auth).
3. **services**: hizmetler, süre, fiyat, kategori, aktiflik.
4. **staff_services**: berber-hizmet ilişkisi (çok-çok).
5. **working_hours**: berber bazlı haftalık çalışma saatleri ve molalar.
6. **time_off**: izin ve kapalı günler.
7. **business_settings**: işletme bilgileri, adres, iptal politikası, randevu ön süresi, maksimum ileri tarih.
8. **customers**: misafir müşteriler (ad, telefon, opsiyonel e-posta, KVKK onay).
9. **appointments**: randevular (staff_id, customer_id, zaman aralığı, durum, onay kodu, notlar, idempotency_key).
10. **appointment_services**: randevuya bağlı hizmetler (anlık fiyat ve süre).
11. **gallery_items**: galeri görselleri.
12. **reviews**: onaylı yorumlar.
13. **announcements**: duyurular.
14. **notifications**: uygulama içi bildirimler.
15. **consents**: KVKK açık rıza.
16. **audit_logs**: kritik değişiklikler.
17. **rate_limits**: forma koruma.

### Kısıtlar ve Güvenlik

- **EXCLUDE kısıtı**: aynı berber için çakışan randevuları (sadece booked durumunda) engeller.
- **RLS**: her tabloda aktif; anonim müşteriler sadece kendi randevusu ve public veriler.
- **Temporal**: tüm zaman damgaları timestamptz, gösterim Europe/Istanbul.
- **Tiggers**: updated_at otomatik güncelleme.
- **Indexler**: berber+tarih, müşteri telefonu, durum.

### Örnek Seed Verisi

- 3 örnek berber
- 8-10 örnek hizmet
- Örnek çalışma saatleri
- Örnek galeri ve yorum

## Güvenlik ve KVKK

- CSRF token koruması
- Prepared statements
- XSS koruması (şablon escaping)
- Rate limiting (randevu, giriş, onay kodu)
- Güvenlik başlıkları (CSP, HSTS vb.)
- KVKK aydınlatma, açık rıza kaydı, veri minimizasyonu, silme/erişim talebi süreci.

## API Endpointleri (Örnek)

- POST /api/appointments (transaction + idempotency)
- GET /api/available-slots?staff_id=&date=&service_id=
- GET /api/appointments/:id (onay kodu ile)
- POST /api/notifications/mark-read
- GET /api/staff/:id

## Frontend Rotaları

- / (anasayfa)
- /hizmetler
- /ekip
- /galeri
- /yorumlar
- /iletisim
- /randevu (4 adımlı akış)
- /randevu-sorgula (telefon + onay kodu)
- /admin (auth gerektirir)

## Performans Hedefleri

- LCP < 2.5s, CLS < 0.1, INP < 200ms
- Görseller WebP/AVIF, lazy loading
- Kritik CSS, minimum JavaScript

## Dağıtım

- Paylaşımlı PHP hosting (ör. cPanel) ve VPS için ayrı rehberler.
- `.env` dosyası ile ortam değişkenleri.
- Supabase URL ve anon/service_role anahtarları.