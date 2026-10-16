<?php $this->extend('layout.php') ?>

<?php
$items = [
    ['Saç Kesimi Dönüşümü', 'Önce ve sonra karşılaştırma.', '/assets/images/gallery1.jpg'],
    ['Sakal Şekillendirme', 'Modern sakal stilleri.', '/assets/images/gallery2.jpg'],
    ['Salon İç Mekan', 'Modern ve konforlu salon ortamımız.', '/assets/images/gallery3.jpg'],
];
?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="eyebrow">Galeri</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">İşçiliğimizden Kareler</h2>
            <p class="mt-4 max-w-xl mx-auto text-muted">Salonumuzdan ve çalışmalarımızdan örnekler.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($items as [$title, $desc, $img]): ?>
            <figure class="card group hover:border-gold/50 transition-colors duration-300">
                <div class="overflow-hidden">
                    <img src="<?= $img ?>" alt="<?= $title ?>" class="w-full h-64 object-cover grayscale group-hover:grayscale-0 group-hover:scale-105 transition duration-500">
                </div>
                <figcaption class="p-5 border-t border-line">
                    <h3 class="font-display text-lg font-semibold text-cream"><?= $title ?></h3>
                    <p class="mt-1 text-sm text-muted"><?= $desc ?></p>
                </figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php $this->stop() ?>
