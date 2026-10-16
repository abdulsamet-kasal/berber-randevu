# Berber Randevu

Açık kaynaklı, profesyonel berber randevu ve yönetim sistemi.

## Özellikler

- Çakışma korumalı randevu oluşturma (EXCLUDE kısıtı ve transaction ile)
- Yönetim paneli (takvim, hizmet, personel, müşteri, galeri, duyurular, raporlar)
- Uygulama içi bildirimler (Supabase Realtime veya polling)
- KVKK uyumlu veri işleme (aydınlatma metni, açık rıza mekanizması)
- Responsive tasarım, mobil öncelikli
- SEO dostu sayfalar
- Rol tabanlı erişim kontrolü (admin, staff, anonim)
- Realtime güncellemeler (Supabase Realtime)
- Ödeme entegrasyonu yok (sadece randevu)
- Çakışma önleme ve idempotency desteği
- Güvenli kimlik doğrulama ve oturum yönetimi

## Teknoloji Yığını

- **Backend**: PHP 8.2+ (Slim Framework)
- **Veritabanı**: Supabase (PostgreSQL + PostgREST + PDO)
- **Kimlik**: Supabase Auth
- **Frontend**: PHP şablonları, Alpine.js, Tailwind CSS (özelleştirilmiş)
- **Test**: PHPUnit, Playwright
- **CI**: GitHub Actions
- **Realtime**: Supabase Realtime

## Mimari Kararlar

Detaylı mimari kararlar için [ARCHITECTURE_DECISIONS.md](ARCHITECTURE_DECISIONS.md) dosyasına bakın.

## Kurulum

1. **Gereksinimler**
   - PHP 8.2+
   - Composer
   - Supabase hesabı

2. **Supabase Projesi Oluşturma**
   - Supabase'te yeni bir proje oluşturun.
   - `.env.example` dosyasını `.env` olarak kopyalayın ve Supabase URL ve anahtarlarını doldurun.

3. **Bağımlılıkları Kurun**
   ```bash
   composer install
   ```

4. **Veritabanı Migrasyonları**
   ```bash
   php supabase/migrate.php
   ```

5. **Seed Verisi**
   ```bash
   php supabase/seed.php
   ```

6. **Yerel Sunucuyu Başlatın**
   ```bash
   php -S localhost:8000 -t public
   ```

7. **Testleri Çalıştırın**
   ```bash
   vendor/bin/phpunit
   npx playwright test
   ```

## Supabase RLS Politikaları

Her tabloda Row Level Security aktif. Politikalar:
- Anonim kullanıcılar sadece kendi randevusu ve public verileri görür.
- Staff sadece kendi randevularını ve ilgili verilerini görür.
- Admin tüm verileri görür ve yönetir.
- Detaylı politika tanımları için [001_init.sql](supabase/migrations/001_init.sql) dosyasına bakın.

## Tasarım Sistemi

- **Logo**: SVG formatında, modern ve Profesyonel
- **Renk Paleti**: 
  - Primary: #4F46E5 (İndigo)
  - Secondary: #10B981 (Yeşil)
  - Accent: #F59E0B (Amber)
- **Tipografi**: Inter (sans-serif) ve Merriweather (serif)
- **Bileşenler**: Kartlar, butonlar, formlar, uyarılar, yükleme göstergeleri
- **Responsive**: Mobil öncelikli tasarım, masaüstü için genişletilebilir
- **Tema**: Açık tema varsayılan, sistem tema tercihini destekler

## Katkı Sağlama

Lütfen `CONTRIBUTING.md` dosyasını okuyun.

## Güvenlik Açığı Bildirimi

Güvenlik açığı bildirimleri için `SECURITY.md` dosyasını okuyun.

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## Kullanılan Araçlar

Kullanılan agency-agents skill'leri: `docs/AGENTS_USED.md` dosyasına bakın.