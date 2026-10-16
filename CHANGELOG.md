# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added (16.10.2026 — test + optimizasyon + admin turu)

- Dosya tabanli katalog cache (`src/Services/Cache.php`, 5 dk TTL; Redis gerektirmez, cPanel uyumlu)
- Katalog API: `GET /api/services`, `GET /api/staff` (cache'li)
- Admin API: randevu araligi (`from`/`to` max 62 gun + `staff_id`/`status` filtreleri),
  satir ici durum guncelleme (`PATCH /api/admin/appointments/{id}`),
  hizmet CRUD (`GET/POST/PATCH/DELETE /api/admin/services`, akilli silme: kullaniliyorsa pasife al),
  usta yonetimi (`GET/POST/PATCH /api/admin/staff`)
- Admin paneli: tarih araligi filtre cubugu (Bugun/Yarin/Bu Hafta/Bu Ay),
  satir ici durum dropdown + Kaydet, Hizmetler/Ustalar yonetim sekmeleri, yeni usta formu
- KVKK aydinlatma sayfasi (`GET /kvkk`)
- `GET /admin` sunucu tarafi cookie guard (yoksa 302 `/admin/giris`);
  login'de `Set-Cookie: br_admin_token` (HttpOnly, 24 saat)
- Randevu sorgulama sayfasi: sonuc karti + sayfa ici ertele/iptal (form submit kaldirildi)
- Server-side TR telefon validation (`AppointmentController::isValidPhone()`)
- `Supabase::rows()` zarf toleransi (string JSON / data zarfi / tek-satir / scalar filtre)
- `supabase/migrations/003_rate_limit_unique.sql` (rate-limit unique kilit; SQL Editor'da bir kez calistirilmali)
- `public/.htaccess` (front-controller rewrite + gzip + browser cache)
- Graph: `07-admin-sequence.mmd` (admin panel akisi)
- Feature testleri: 6 yeni test (aralik validation, usta validation, rows regresyonlari dahil) — **PHPUnit 19/19**

### Changed

- RateLimitMiddleware rewrite: gunluk pencere + tek upsert + %1 cleanup + `/assets/*` bypass
- `available-slots`: sadece ilgili gunun randevularini ceker (buyuk veride hizli)
- Seed idempotent: `ON CONFLICT (id) DO NOTHING` (customers, business_settings)
- Auth akisi: cookie + Bearer cift katman
- Placeholder gorseller: 0-byte JPG'ler yerine SVG placeholder (7 dosya)

### Fixed

- Seeded inquiry 404 (idempotent seed ile veri tutarliligi)
- `/admin` client-side-only guard → server-side cookie guard
- Panel butonlari: "Hizmetleri Duzenle"/"Usta Yonetimi" public sayfalara gidiyordu → panel ici `#yonetim`
- `btn-secondary`/`btn-danger` CSS'te tanimsizdi → `input.css` + derlenmis `app.css`'e eklendi

## [0.1.0] - 2026-10-15

### Added
- Basic project structure
- MIT License
- .gitignore
- README.md
- Architecture documentation
- Environment example
- Composer configuration
- PHPStan configuration
- GitHub Actions CI workflow
- Database migrations with RLS policies and EXCLUDE constraint
- Seed data with example barbers, services, working hours
- Slim Framework based application skeleton
- Twig-like templating system with layout inheritance
- API controllers for appointments and authentication
- View rendering system
- Slot engine for availability checking
- Appointment service with transaction support and idempotency
- Basic UI templates (home, services, team, gallery, reviews, contact, appointment booking, inquiry)
- Admin login template
- Placeholder assets (images, CSS, JavaScript)
- Design system with CSS variables and responsive layout
- NEXT_STEPS.md with setup instructions
- Basic unit and feature tests
- Basic E2E tests with Playwright

### Changed
- N/A

### Deprecated
- N/A

### Removed
- N/A

### Fixed
- N/A

### Security
- Added RLS policies to all tables
- Implemented EXCLUDE constraint to prevent double bookings
- Prepared for CSRF token implementation
- Structured for input validation and output escaping