<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="eyebrow">Yasal Bilgilendirme</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">KVKK Aydınlatma Metni</h2>
        </div>

        <div class="card p-8 md:p-10 space-y-6 text-muted leading-relaxed">
            <p>
                Berber Randevu olarak, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK")
                uyarınca veri sorumlusu sıfatıyla, randevu oluşturma ve sorgulama süreçlerinde
                paylaştığınız kişisel verileri işlemekteyiz.
            </p>

            <h3 class="font-display text-xl font-semibold text-cream">1. İşlenen Veriler</h3>
            <p>
                Ad-soyad, telefon numarası, e-posta adresi (isteğe bağlı), randevu bilgileri
                (tarih, saat, hizmet, usta) ve randevu notlarınız işlenmektedir.
            </p>

            <h3 class="font-display text-xl font-semibold text-cream">2. İşleme Amaçları</h3>
            <ul class="list-disc pl-6 space-y-2">
                <li>Randevunuzun oluşturulması, onaylanması, hatırlatılması ve yönetilmesi</li>
                <li>Randevu sorgulama ve iptal taleplerinizin yerine getirilmesi</li>
                <li>Hizmet kalitesinin artırılması ve müşteri memnuniyeti ölçümü</li>
                <li>Yasal yükümlülüklerin yerine getirilmesi</li>
            </ul>

            <h3 class="font-display text-xl font-semibold text-cream">3. Hukuki Sebep</h3>
            <p>
                Verileriniz, randevu formunda verdiğiniz açık rızanıza (KVKK m.5/1) ve
                sözleşmenin kurulması/ifası gerekliliğine (KVKK m.5/2-c) dayanılarak işlenmektedir.
                Rıza vermeden randevu oluşturulamaz.
            </p>

            <h3 class="font-display text-xl font-semibold text-cream">4. Saklama Süresi</h3>
            <p>
                Randevu verileriniz, hizmetin tamamlanmasını takiben yasal zamanaşımı süreleri
                boyunca (en fazla 5 yıl) saklanır ve ardından silinir, yok edilir veya anonim
                hale getirilir.
            </p>

            <h3 class="font-display text-xl font-semibold text-cream">5. Haklarınız</h3>
            <p>
                KVKK m.11 kapsamında; verilerinize erişme, düzeltme, silme, işlemeye itiraz etme
                ve zararın giderilmesini talep etme haklarına sahipsiniz. Taleplerinizi
                <a href="/iletisim" class="text-gold hover:text-gold-light transition-colors">iletişim sayfamız</a>
                üzerinden bize iletebilirsiniz.
            </p>
        </div>
    </div>
</section>
<?php $this->stop() ?>
