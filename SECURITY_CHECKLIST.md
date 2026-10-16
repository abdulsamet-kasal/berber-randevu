# Berber Randevu Güvenlik Kontrol Listesi

Bu doküman, Berber Randevu uygulaması için uygulanması gereken güvenlik önlemlerini ve kontrollerini listeler.

## 1. Kimlik Doğrulama ve Yetkilendirme

- [x] Supabase Auth kullanarak güvenli kimlik doğrulama
- [ ] Şifre politikası (minimum 8 karakter, büyük/küçük harf, sayı, özel karakter)
- [ ] İki faktörlü kimlik doğrulama (2FA) seçeneği
- [ ] Oturum zaman aşımı ve güvenli otorun yönetimi
- [ ] "Beni hatırla" işlevi için güvenli token yönetimi
- [ ] Şifre sıfırlama işlevi için güvenli token ve zaman aşımı
- [ ] Hesap kilitleme mekanizması (başarısız giriş denemeleri sonrası)

## 2. Veri Güvenliği

- [x] Supabase üzerinden veri şifreleme (HTTPS/TLS)
- [ ] Hassas verilerin veritabanında şifrelenmesi (ör. telefon numaraları)
- [ ] Veritabanı yedeklerinin şifrelenmesi ve güvenli konumda saklanması
- [ ] Veri erişim kontrolü ve ayrıcalık azaltma prinsibi
- [ ] Veritabanı bağlantıların güvenli şekilde yönetilmesi

## 3. Uygulama Güvenliği

- [x] SQL injection koruması için hazır ifadeler (prepared statements)
- [x] XSS (Cross-Site Scripting) koruması için çıktı kodlama
- [ ] CSRF (Cross-Site Request Forgery) koruması için token tabanlı koruma
- [ ] HTTP güvenlik başlıkları (CSP, HSTS, X-Frame-Options, vb.)
- [ ] Girdi doğrulama ve temizleme (input validation and sanitization)
- [ ] Dosya yükleme kontrolleri (tip, boyut, virüs taraması)
- [ ] Dizin listelemeyi devre dışı bırakma
- [ ] Sunucu tarafı içerik yönetimi için güvenli yapılandırma

## 4. Ağ ve Altyapı Güvenliği

- [ ] Web uygulama güvenlik duvarı (WAF) kullanımı
- [ ] DDoS koruması
- [ ] Güvenli SSL/TLS yapılandırması
- [ ] Sık güncellenen ve yamalanan sistem bileşenleri
- [ ] Ağ segmentasyonu ve güvenli zona yerleştirme
- [ ] Güvenlik izleme ve log analizi (SIEM)

## 5. Geliştirme ve Dağıtım Güvenliği

- [ ] Güvenli kod yazma standartları
- [ ] Statik uygulama güvenliği testi (SAST)
- [ ] Dinamik uygulama güvenliği testi (DAST)
- [ ] Bağımlılık güvenliği taraması
- [ ] Güvenli sürüm kontrolü kullanımı
- [ ] Üretim ortamında gizli bilgilerin环境变量中存储
- [ ] Otomatik güvenlik güncellemeleri ve yamalar

## 6. Veri Gizliliği ve KVKK Uyumu

- [x] KVKK aydınlatma metni uygulanması
- [x] Açık rıza mekanizması uygulanması
- [ ] Veri minimization prinsibi (sadece gerekli verilerin toplanması)
- [ ] Veri erişim ve измененияのログ記録
- [ ] Veri silme ve anonymization prosedürleri
- [ ] Veri taşıma sözleşmeleri (Data Processing Agreements)
- [ ] Göreli veri koruması 책임人的 지정

## 7. İzleme ve Log Kaydı

- [x] Audit log tablosu oluşturulması
- [ ] Kritik işlemlerin loglanması (giriş denemeleri, veri değişiklikleri, vb.)
- [ ] Güvenlik olaylarının gerçek zamanlı izlenmesi
- [ ] Logların merkezi bir sistemde toplanması ve analiz edilmesi
- [ ] Log tutma süresi politikası
- [ ] Logların değiştirilemez hale getirilmesi (append-only)

## 8. Acil Durum ve Yedekleme Planları

- [ ] Veri yedekleme ve geri yükleme prosedürleri
- [ ] Acil durum yanıt planı (IR Planı)
- [ ] Güvenlik eventi eşliğinde iletişim planı
- [ ] Sistem gücünü geri alma süresi hedefleri (RTO)
- [ ] Veri kaybını kabul edilebilir seviyede tutma hedefleri (RPO)

## 9. Eğitim ve Farkındalık

- [ ] Güvenlik eğitimi (geliştirici ve operatörler için)
- [ ] Fishing ve sosyal mühendislik konularında farkındalık
- [ ] Güvenlik politikalarının düzenli güncellenmesi ve bildirilmesi
- [ ] Güvenlik denetleri ve penetrasyon testleri

## 10. Test ve Doğrulama

- [ ] Güvenlik açığı taraması ve penetrasyon testi
- [ ] Ücretli güvenlik chercheurs tarafından bağımsız değerlendirme
- [ ] Güvenlik incidenti simülasyonları ve müdahale testleri
- [ ] Güvenlik kontrol listeninin düzenli gözden geçirilmesi

Her kontrollü öğenin uygulama durumu, uygulamanın güvenlik seviyesini gösterir. 
[ ] olmayan öğeler, tamamlanması gereken görevleri gösterir.