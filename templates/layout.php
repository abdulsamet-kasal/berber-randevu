<?php
if (!function_exists('html_escape')) {
    function html_escape(mixed $string): string
    {
        return htmlspecialchars((string) $string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navItems = [
    ['/hizmetler', 'Hizmetler'],
    ['/ekip', 'Ekip'],
    ['/galeri', 'Galeri'],
    ['/yorumlar', 'Yorumlar'],
    ['/iletisim', 'İletişim'],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Berber Randevu') ?></title>
    <meta name="description" content="<?= html_escape($description ?? '') ?>">
    <link rel="icon" href="/assets/logo.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-ink text-cream font-sans min-h-screen flex flex-col">

    <header class="sticky top-0 z-40 bg-ink/90 backdrop-blur border-b border-line">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/assets/logo.svg" alt="Berber Randevu" class="h-9 w-9">
                    <span class="font-display text-xl font-bold tracking-wide text-cream group-hover:text-gold transition-colors">
                        Berber<span class="text-gold">.</span>
                    </span>
                </a>

                <nav class="hidden md:flex items-center gap-8">
                    <?php foreach ($navItems as [$href, $label]): ?>
                        <?php $active = $currentPath === $href; ?>
                        <a href="<?= $href ?>"
                           class="text-sm font-semibold tracking-wider uppercase transition-colors <?= $active ? 'text-gold' : 'text-muted hover:text-cream' ?>">
                            <?= $label ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="hidden md:flex items-center gap-4">
                    <a href="/admin/giris" class="text-sm font-semibold text-muted hover:text-cream transition-colors tracking-wider uppercase">Admin</a>
                    <a href="/randevu" class="btn-primary">Randevu Al</a>
                </div>

                <button type="button" data-mobile-menu-button class="md:hidden text-cream p-2" aria-label="Menü">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
        <div data-mobile-menu class="hidden md:hidden border-t border-line bg-coal">
            <nav class="px-4 py-4 flex flex-col gap-1">
                <?php foreach ($navItems as [$href, $label]): ?>
                    <a href="<?= $href ?>" class="py-3 px-2 text-sm font-semibold tracking-wider uppercase <?= $currentPath === $href ? 'text-gold' : 'text-muted hover:text-cream' ?>"><?= $label ?></a>
                <?php endforeach; ?>
                <a href="/admin/giris" class="py-3 px-2 text-sm font-semibold tracking-wider uppercase text-muted hover:text-cream">Admin Giriş</a>
                <a href="/randevu" class="btn-primary mt-3">Randevu Al</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <?= $content ?? '' ?>
    </main>

    <footer class="border-t border-line bg-coal mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div>
                    <a href="/" class="flex items-center gap-3">
                        <img src="/assets/logo.svg" alt="Berber Randevu" class="h-8 w-8">
                        <span class="font-display text-lg font-bold text-cream">Berber<span class="text-gold">.</span></span>
                    </a>
                    <p class="mt-4 text-sm text-muted leading-relaxed">
                        Ustalık, özen ve stil. Profesyonel berber randevu ve yönetim sistemi.
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-bold tracking-[0.25em] uppercase text-gold mb-4">Keşfet</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/hizmetler" class="text-muted hover:text-gold transition-colors">Hizmetlerimiz</a></li>
                        <li><a href="/ekip" class="text-muted hover:text-gold transition-colors">Ekibimiz</a></li>
                        <li><a href="/galeri" class="text-muted hover:text-gold transition-colors">Galeri</a></li>
                        <li><a href="/randevu" class="text-muted hover:text-gold transition-colors">Randevu Al</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold tracking-[0.25em] uppercase text-gold mb-4">Destek</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/iletisim" class="text-muted hover:text-gold transition-colors">İletişim</a></li>
                        <li><a href="/randevu-sorgula" class="text-muted hover:text-gold transition-colors">Randevu Sorgula</a></li>
                        <li><a href="/kvkk" class="text-muted hover:text-gold transition-colors">KVKK Bilgilendirme</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-bold tracking-[0.25em] uppercase text-gold mb-4">İletişim</h4>
                    <ul class="space-y-3 text-sm text-muted">
                        <li>+90 212 555 12 34</li>
                        <li>info@berberrandevu.example</li>
                        <li>Örnek Cadde No:123, İstanbul</li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-6 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-text-muted tracking-wide">© <?= date('Y') ?> Berber Randevu. Tüm hakları saklıdır.</p>
                <p class="text-xs text-text-muted tracking-[0.2em] uppercase">Ustalık &amp; Stil</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js" defer></script>
</body>
</html>
