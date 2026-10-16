# Bir sonraki adımlar

Bu proje, berber randevu sistemi için temel bir iskelet sağlar. Uygulamayı tam olarak çalışır hale getirmek için aşağıdaki adımları izlemeniz gerekir.

## 1. PHP Ortamını Ayarlayın

- PHP 8.2+ yüklü olduğundan emin olun.
- OpenSSL eklentisini etkinleştirin (Composer ve güvenli HTTPS transfers için gerekli).
  - `php.ini` dosyanızı bulun ve aşağıdaki satırların yorumunu kaldırın:
    ```
    extension=openssl
    extension=curl
    ```
  - Alternatif olarak, Composer'ı `--disable-tls` seçeneğiyle kullanabilirsiniz (güvensiz).

## 2. Bağımlılıkları Yükleyin

Composer'ı yükleyin (eğer yüklü değilse) ve bağımlılıkları yükleyin:

```bash
# Composer'ı yükle (eğer yoksa)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --disable-tls
php -r "unlink('composer-setup.php');"

# Bağımlılıkları yükle (TLS devre dışı bırakarak)
php composer.phar install --disable-tls
```

> **Not**: Gerçek bir üretim ortamında OpenSSL etkin olmalıdır. Bu geçici bir çözümdür.

## 3. Supabase Projesi Oluşturun

1. [Supabase](https://supabase.com) hesabı oluşturun ve yeni bir proje başlatın.
2. Proje ayarlarından:
   - **URL** ve **anon public** anahtarını kopyalayın.
   - **Service role** anahtarını kopyalayın (güvenli bir yerde saklayın).
   - Veritabanı bağlantı stringini (PostgreSQL) almak için:
     - Settings → Database → Connection string → URI
     - Format: `postgresql://postgres:[YOUR-PASSWORD]@db.[YOUR-PROJECT].supabase.co:5432/postgres`

## 4. Ortam Değişkenlerini Ayarlayın

- `.env.example` dosyasını `.env` olarak kopyalayın.
- `.env` dosyasını düzenleyerek aşağıdaki değişkenleri Supabase projenizden alınan değerlerle doldurun:
  ```
  SUPABASE_URL=your_supabase_project_url
  SUPABASE_ANON_KEY=your_supabase_anon_public_key
  SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key
  DATABASE_URL=postgresql://postgres:[YOUR-PASSWORD]@db.[YOUR-PROJECT].supabase.co:5432/postgres
  ```

## 5. Veritabanı Migrasyonlarını Çalıştırın

Supabase veritabanına migrasyonları uygulayın:

```bash
php supabase/migrate.php
```

Bu komut, `supabase/migrations/` klasöründeki SQL dosyalarını çalıştıracaktır.

## 6. Örnek Seed Verisini Yükleyin

Örnek verileri veritabanına yükleyin:

```bash
php supabase/seed.php
```

Bu komut, örnek berberler, hizmetler, çalışma saatleri, galeri öğeleri ve randevu verilerini ekleyecektir.

## 7. Uygulamayı Yerel Olarak Çalıştırın

PHP'nin yerel geliştirme sunucusunu başlatın:

```bash
php -S localhost:8000 -t public
```

Tarayıcınızda `http://localhost:8000` adresine giderek uygulamayı görüntüleyebilirsiniz.

## 8. Testleri Çalıştırın

Birim ve entegrasyon testlerini çalıştırın:

```bash
# PHPUnit testleri
vendor/bin/phpunit

# Playwright E2E testleri (Node.js ve Playwright gerektirir)
npx playwright test
```

> **Not**: Testler için veritabanı bağlantısının ayarlandığından emin olun. Test ortamı için ayrı bir Supabase projeyi veya veritabanını kullanmanız önerilir.

## 9. Üretime Dağıtım

Uygulamayı üretime dağıtmak için:
- PHP destekleyen bir hosting sağlayıcısı (cPanel, Plesk, vb.) veya bir VPS kullanın.
- `.env` dosyasındaki değişkenleri üretim ortamı değerleriyle güncelleyin.
- HTTPS etkinleştirin (Let's Encrypt ücretsiz SSL sertifikası sağlar).
- Güncellemeleri GitHub üzerinden çekin ve `composer install` çalıştırın.
- Gerekiyorsa önbelleği temizleyin ve oturum süreçlerini yapılandırın.

## 10. Ek Notlar

- Bu iskelet, auth, randevu oluşturma, sorgulama ve iptal işlevlerinin temel örneklerini içerir.
- Gerçek bir uygulama için:
  - Randevu çakışma kontrolü (EXCLUDE kısıtı ve slot motoru) tamamlanmalıdır.
  - Kimlik doğrulama ve oturum yönetimi güçlendirilmelidir.
  - Form validasyonu ve CSRF koruması eklenmelidir.
  - Uygulama içi bildirimler (Supabase Realtime veya polling) uygulanmalıdır.
  - Admin paneli özellikleri (takvim, raporlar, vb.) tamamlanmalıdır.
- KVKK uyumluluğu için:
  - Aydınlatma metni ve açık rıza mekanizmaları uygulanmalıdır.
  - Veri silme/erişim talebi süreçleri tanımlanmalıdır.

## Yardım ve Destek

Sorunlarınız olursa, GitHub issue tracker üzerinden bildirebilirsiniz.

---

**Not**: Bu dosya, projeyi çalışır hale getirmek için gereken minimum adımları kapsar. Daha detaylı bilgiler için `README.md` ve `docs/ARCHITECTURE.md` dosyalarını inceleyin.