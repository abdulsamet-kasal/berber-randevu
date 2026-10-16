<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>

<!-- Hero -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-surface via-ink to-ink"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-36 text-center">
        <span class="eyebrow">Ustalık &amp; Stil</span>
        <h1 class="mt-6 font-display text-5xl md:text-7xl font-bold leading-tight text-cream">
            Keskin Görünüm,<br>
            <em class="text-gold not-italic font-display italic">Kusursuz</em> Detay.
        </h1>
        <p class="mt-6 max-w-2xl mx-auto text-lg text-muted leading-relaxed">
            Sıra beklemek yok. Hizmetini seç, ustanı belirle, saatini kap.
            Koltuğa oturduğunda her şey hazır olsun.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/randevu" class="btn-primary w-full sm:w-auto">
                Hemen Randevu Al
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <a href="/hizmetler" class="btn-outline w-full sm:w-auto">Hizmetleri Keşfet</a>
        </div>
    </div>
</section>

<!-- Özellikler -->
<section class="border-t border-line">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid md:grid-cols-3 gap-px bg-line border border-line">
            <div class="bg-ink p-10 text-center">
                <p class="font-display text-5xl font-bold text-gold">01</p>
                <h3 class="mt-4 font-display text-xl font-semibold text-cream">Hizmetini Seç</h3>
                <p class="mt-2 text-sm text-muted leading-relaxed">Saç kesiminden sakal tasarımına, ihtiyacına uygun hizmetleri belirle.</p>
            </div>
            <div class="bg-ink p-10 text-center">
                <p class="font-display text-5xl font-bold text-gold">02</p>
                <h3 class="mt-4 font-display text-xl font-semibold text-cream">Ustanı Belirle</h3>
                <p class="mt-2 text-sm text-muted leading-relaxed">Deneyimli ustalarımız arasından sana uygun olanı seç.</p>
            </div>
            <div class="bg-ink p-10 text-center">
                <p class="font-display text-5xl font-bold text-gold">03</p>
                <h3 class="mt-4 font-display text-xl font-semibold text-cream">Saatini Kap</h3>
                <p class="mt-2 text-sm text-muted leading-relaxed">Uygun gün ve saati seç, onay kodunla randevunu anında al.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA kartları -->
<section class="border-t border-line bg-coal">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-12">
            <span class="eyebrow">Başlayalım</span>
            <h2 class="mt-4 font-display text-3xl md:text-4xl font-bold text-cream">Koltuk Hazır, Sıra Sende</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="card p-10 flex flex-col">
                <h3 class="font-display text-2xl font-semibold text-cream">Online Randevu</h3>
                <p class="mt-3 text-muted leading-relaxed flex-1">
                    Üç adımda randevunu oluştur; hizmet, usta ve saat seçimi hepsi bir arada.
                </p>
                <a href="/randevu" class="btn-primary mt-8 self-start">Randevu Al</a>
            </div>
            <div class="card p-10 flex flex-col">
                <h3 class="font-display text-2xl font-semibold text-cream">Randevunu Yönet</h3>
                <p class="mt-3 text-muted leading-relaxed flex-1">
                    Telefon numaran ve onay kodunla randevunu sorgula, ertele ya da iptal et.
                </p>
                <a href="/randevu-sorgula" class="btn-outline mt-8 self-start">Randevu Sorgula</a>
            </div>
        </div>
    </div>
</section>

<!-- İstatistik bandı -->
<section class="border-t border-line">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <p class="font-display text-4xl font-bold text-gold">10+</p>
                <p class="mt-1 text-xs tracking-[0.2em] uppercase text-muted">Yıllık Deneyim</p>
            </div>
            <div>
                <p class="font-display text-4xl font-bold text-gold">3</p>
                <p class="mt-1 text-xs tracking-[0.2em] uppercase text-muted">Uzman Usta</p>
            </div>
            <div>
                <p class="font-display text-4xl font-bold text-gold">5.0</p>
                <p class="mt-1 text-xs tracking-[0.2em] uppercase text-muted">Müşteri Puanı</p>
            </div>
            <div>
                <p class="font-display text-4xl font-bold text-gold">1000+</p>
                <p class="mt-1 text-xs tracking-[0.2em] uppercase text-muted">Mutlu Müşteri</p>
            </div>
        </div>
    </div>
</section>
<?php $this->stop() ?>
