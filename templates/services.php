<?php $this->extend('layout.php') ?>

<?php
$services = [
    ['Saç Kesimi', 'Klasik ve modern kesimler; saç tipinize ve yüz hatlarınıza uygun özel stil.', '150 ₺', '30 dakika'],
    ['Sakal Şekillendirme', 'Sakal trim, hat çekimi ve bakım ile keskin bir görünüm.', '120 ₺', '25 dakika'],
    ['Tırnak Bakımı', 'Profesyonel el ve tırnak bakımı ile bakımlı bir görünüm.', '80 ₺', '20 dakika'],
    ['Kombine Paket', 'Saç + sakal + bakım. Tüm ihtiyaçlarınız tek seansta.', '300 ₺', '60 dakika'],
];
?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="eyebrow">Menümüz</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">Hizmetlerimiz</h2>
            <p class="mt-4 max-w-xl mx-auto text-muted">Her hizmet, ustalık ve özenle sunulur. Fiyatlar sabittir, sürpriz yoktur.</p>
        </div>

        <div class="grid gap-8 sm:grid-cols-2">
            <?php foreach ($services as [$name, $desc, $price, $duration]): ?>
            <div class="card group hover:border-gold/50 transition-colors duration-300">
                <div class="p-8 flex flex-col h-full">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="font-display text-2xl font-semibold text-cream group-hover:text-gold transition-colors"><?= $name ?></h3>
                        <p class="font-display text-2xl font-bold text-gold whitespace-nowrap"><?= $price ?></p>
                    </div>
                    <p class="mt-3 text-muted leading-relaxed flex-1"><?= $desc ?></p>
                    <div class="mt-6 pt-4 border-t border-line flex items-center justify-between">
                        <span class="text-xs tracking-[0.2em] uppercase text-text-muted"><?= $duration ?></span>
                        <a href="/randevu" class="text-sm font-bold text-gold hover:text-gold-light tracking-wide uppercase transition-colors">Randevu Al &rarr;</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-16 text-center">
            <a href="/randevu" class="btn-primary">Hemen Randevu Oluştur</a>
        </div>
    </div>
</section>
<?php $this->stop() ?>
