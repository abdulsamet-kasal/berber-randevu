<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="eyebrow">İletişim</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">Bize Ulaşın</h2>
        </div>

        <div class="grid gap-10 lg:grid-cols-2">
            <div class="card p-10">
                <h3 class="font-display text-2xl font-semibold text-cream">Salon Bilgileri</h3>
                <p class="mt-3 text-muted leading-relaxed">
                    Sorularınız, önerileriniz ve randevu talepleriniz için bize her zaman ulaşabilirsiniz.
                </p>
                <dl class="mt-8 space-y-6">
                    <div class="flex gap-4">
                        <dt class="text-xs font-bold tracking-[0.2em] uppercase text-gold w-24 pt-1">Adres</dt>
                        <dd class="text-cream">Örnek Cadde No:123, İstanbul</dd>
                    </div>
                    <div class="flex gap-4">
                        <dt class="text-xs font-bold tracking-[0.2em] uppercase text-gold w-24 pt-1">Telefon</dt>
                        <dd class="text-cream">+90 212 555 12 34</dd>
                    </div>
                    <div class="flex gap-4">
                        <dt class="text-xs font-bold tracking-[0.2em] uppercase text-gold w-24 pt-1">Saatler</dt>
                        <dd class="text-cream">Pzt–Cum 09:00–19:00<br>Cmt 09:00–17:00</dd>
                    </div>
                </dl>
                <div class="mt-10 pt-6 border-t border-line">
                    <a href="/randevu" class="btn-primary">Online Randevu Al</a>
                </div>
            </div>

            <div class="card overflow-hidden min-h-[24rem]">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d28.9795!3d41.0123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!2f0!2f0!3m2!1i1024!2i768"
                    width="100%" height="100%" style="border:0; min-height:24rem; filter: grayscale(1) contrast(1.1);"
                    allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Salon Konumu">
                </iframe>
            </div>
        </div>
    </div>
</section>
<?php $this->stop() ?>
