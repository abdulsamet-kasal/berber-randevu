<?php $this->extend('layout.php') ?>

<?php
$reviews = [
    ['Ali Yılmaz', 5, 'Harika bir berber deneyimi, kesinlikle tavsiye ederim! Usta Ahmet çok profesyonel ve işinin ehli.', '2026-10-15'],
    ['Mert Kaya', 5, 'Randevu sistemi sayesinde sıra beklemeden hizmet aldım. Sakal şekillendirme bire bir istediğim gibi oldu.', '2026-10-02'],
    ['Emre Demir', 4, 'Salon temiz ve ferah, ekip ilgili. Kombine paket fiyatına göre çok başarılı.', '2026-09-20'],
];
?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="eyebrow">Yorumlar</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">Müşterilerimiz Ne Diyor?</h2>
        </div>

        <div class="space-y-6">
            <?php foreach ($reviews as [$name, $rating, $comment, $date]): ?>
            <div class="card">
                <div class="p-8">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h3 class="font-display text-lg font-semibold text-cream"><?= $name ?></h3>
                        <div class="flex gap-1 text-gold text-lg leading-none" aria-label="<?= $rating ?> yıldız">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <span><?= $i < $rating ? '★' : '☆' ?></span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="mt-4 text-muted leading-relaxed">&ldquo;<?= $comment ?>&rdquo;</p>
                    <p class="mt-4 text-xs tracking-[0.2em] uppercase text-text-muted"><?= $rating ?> yıldız &bull; <?= $date ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 text-center">
            <a href="/randevu" class="btn-outline">Siz de Deneyimleyin</a>
        </div>
    </div>
</section>
<?php $this->stop() ?>
