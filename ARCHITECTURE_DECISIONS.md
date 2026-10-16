# Architecture Decision Records

Bu doküman, Berber Randevu projesi sırasında alınan mimari kararları kaydeder.

## ADR 001: Teknoloji Yığını Seçimi

**Durum**: Kabul Edildi  
**Tarih**: 2026-10-15

### Bağlam
Berber Randevu uygulaması için uygun bir teknoloji yığını seçmek gerekliydi. Proje şu gereksinimleri karşılamalıydı:
- Hafif ve hızlı bir backend
- Gerçek zamanlı bildirim yeteneği
- Güvenli ve ölçeklebilir veritabanı
- Basit dağıtım ve bakım
- Açık kaynaklı ve ücretsiz seçenekler

### Karar
Aşağıdaki teknoloji yığını seçildi:
- **Backend**: PHP 8.2 + Slim Framework
- **Veritabanı ve Kimlik**: Supabase (PostgreSQL + Supabase Auth + Row Level Security)
- **Frontend**: Sunucu taraflı render edilmiş PHP şablonları, Alpine.js, Tailwind CSS
- **CI/CD**: GitHub Actions

### Sonuçlar
**Olumlu**:
- Hafif ve hızlı geliştirme süreci
- Supabase tarafından sağlanan gerçek zamanlı bildirim yeteneği
- Güçlü ve ölçeklebilir PostgreSQL veritabanı
- Basit dağıtım (PHP destekleyen herhangi bir sunucu)
- Güçlü topluluk desteği ve dokümantasyon

**Olumsuz**:
- SPAframework'lerin sunduğu bazı etkileşim avantajları kaybedildi
- Bazı karmaşık etkileşimler için ek JavaScript gerekebilir

### Bağlantılar
- [Mimari Plan](ARCHITECTURE.md)

## ADR 002: Veritabanı Tasarım Yaklaşımı

**Durum**: Kabul Edildi  
**Tarih**: 2026-10-15

### Bağlam
Berber Randevu uygulaması için veritabanı şeması tasarlanırken şu faktörler göz önünde bulunduruldu:
- Randevu çakışmalarının önlenmesi
- Ölçeklenebilirlik ve performans
- Esneklik (hizmet, berber, çalışma saatleri değişiklikleri)
- Güvenlik ve gizlilik (KVKK uyumu)
- Raporlama ve analiz yeteneği

### Karar
Aşağıdaki veritabanı tasarım yaklaşımı benimsendi:
- UUID primary key'leri kullanılması (güvenlik ve ölçeklenebilirlik için)
- Row Level Security (RLS) politikalarının uygulanması
- Çakışma önlemesi için EXCLUDE kısıtının kullanımı (btree_gist extension)
- Normalizasyon üzerinden veri bütünlüğünün sağlanması
- Anlik görüntü (snapshot) tablolarının kullanımı (fiyat değişikliklerinin geçmiş randevuları etkilememesi)
- Zamana damgalarının timestamptz olarak 저장되고 Europe/Istanbul'da gösterilmesi

### Sonuçlar
**Olumlu**:
- Güçlü veri bütünlüğü ve tutarlılığı
- Randevu çakışmalarının veritabanı seviyesinde önlenmesi
- Esnek ve ölçeklebilir şema tasarımı
- KVKK uyumlu veri erişim kontrolleri
- Daha iyi sorgu performansı için uygun indeksler

**Olumsuz**:
- UUID'ler integer primary key'lerden daha fazla depolama alanı kullanır
- Daha karmaşık sorgu yazımı gerekebilir

### Bağlantılar
- [Veritabanı Migrasyonu](supabase/migrations/001_init.sql)
- [Mimari Plan](ARCHITECTURE.md)

## ADR 003: Randevu Akışı ve Teknik Kararlar

**Durum**: Kabul Edildi  
**Tarih**: 2026-10-15

### Bağlam
Randevu akışı uygulaması sırasında şu teknik gereksinimleri karşılmak zorundaydık:
- Çakışma koruması (race condition durumlarına karşı son savunma hattı)
- Idempotency (tekrar eden isteklerin aynı randevu oluşturmaması)
- Gerçek zamanlı müsaitlik hesabı
- Minimum ön rezervasyon süresi ve maksimum ileri tarih penceresi
- Hizmet süresi, berber çalışma saatleri, molalar, izinler gibi faktörlerin dikkate alınması

### Karar
Aşağıdaki teknik kararlar alındı:
- Randevu oluşturma işlemi için veritabanı transaction'ı kullanılması
- EXCLUDE kısıtını kullanarak çakışmayı veritabanı seviyesinde önlemek
- Her randevu oluşturma isteğinde benzersiz idempotency key üretimi ve kontrolü
- Müsaitlik hesabının sunucu tarafında yapılması ve kısa süreli önbelleklemesi
- Slot motoru üzerinden çalışma saatleri, molalar, izinler, mevcut randevular ve hizmet süresinin dikkate alınması
- Zaman dilimi dönüşümü (UTC veritabanı, Europe/Istanbul gösterim)

### Sonuçlar
**Olumlu**:
- Çakışma koruması veritabanı seviyesinde sağlandığı için race condition'lara karşı güçlü koruma
- Idempotency key sistemi sayesinde tekrar eden istekler aynı randevu oluşturmaz
- Müsaitlik hesabının sunucu tarafında yapılması güvenilirliği artırır
- Esnek slot motoru farklı iş kurallarını kolayca entegre etmeyi mümkün kılar

**Olumsuz**:
- Transaction kullanımı biraz daha fazla veritabanı yükü oluşturabilir
- Idempotency key yönetimi ekstra karmaşıklık getirir

### Bağlantılar
- [Mimari Plan](ARCHITECTURE.md)
- [Slot Motoru](src/Slot/SlotEngine.php)
- [Randevu Servisi](src/Appointment/AppointmentService.php)