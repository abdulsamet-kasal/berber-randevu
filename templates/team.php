<?php $this->extend('layout.php') ?>

<?php
$team = [
    ['Usta Ahmet', 'Kıdemli Usta', '10 yıl deneyim. Klasik kesimler ve eski usul ustura işi onun uzmanlığı.', '/assets/images/team1.jpg'],
    ['Usta Mehmet', 'Stilist', 'Sakal tasarımı ve modern stiller konusunda yaratıcı dokunuşlar.', '/assets/images/team2.jpg'],
    ['Usta Ali', 'Berber', 'Genç ve dinamik; trend kesimlerde salonun gözdesi.', '/assets/images/team3.jpg'],
];
?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="eyebrow">Ekibimiz</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">Ustalarla Tanışın</h2>
            <p class="mt-4 max-w-xl mx-auto text-muted">Her biri kendi alanında uzman, işini tutkuyla yapan bir ekip.</p>
        </div>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($team as [$name, $role, $bio, $img]): ?>
            <div class="card group text-center hover:border-gold/50 transition-colors duration-300">
                <div class="p-10">
                    <div class="relative inline-block">
                        <img src="<?= $img ?>" alt="<?= $name ?>" class="w-28 h-28 rounded-full mx-auto object-cover border-2 border-gold/40 group-hover:border-gold transition-colors">
                    </div>
                    <h3 class="mt-6 font-display text-xl font-semibold text-cream"><?= $name ?></h3>
                    <p class="mt-1 text-xs font-bold tracking-[0.25em] uppercase text-gold"><?= $role ?></p>
                    <p class="mt-4 text-sm text-muted leading-relaxed"><?= $bio ?></p>
                    <a href="/randevu" class="mt-6 inline-block text-sm font-bold text-cream hover:text-gold tracking-wide uppercase transition-colors">Bu Ustayla Randevu &rarr;</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php $this->stop() ?>
