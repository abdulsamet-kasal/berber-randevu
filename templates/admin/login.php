<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="eyebrow">Yönetim</span>
            <h2 class="mt-4 font-display text-4xl font-bold text-cream">Admin Girişi</h2>
        </div>

        <div class="card p-8">
            <form id="adminLoginForm" action="/api/admin/login" method="POST">
                <div class="space-y-5">
                    <div>
                        <label for="adminEmail" class="label">E-posta</label>
                        <input type="email" id="adminEmail" name="email" class="input" required>
                    </div>
                    <div>
                        <label for="adminPassword" class="label">Şifre</label>
                        <input type="password" id="adminPassword" name="password" class="input" required>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-muted cursor-pointer">
                            <input type="checkbox" id="rememberMe" name="remember" class="accent-gold h-4 w-4"> Beni hatırla
                        </label>
                        <a href="#" class="font-semibold text-gold hover:text-gold-light transition-colors">Şifremi unuttum</a>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full mt-8">Giriş Yap</button>
            </form>
            <p class="mt-6 text-sm text-muted text-center">
                Hesabınız yok mu? <a href="#" class="font-semibold text-gold hover:text-gold-light transition-colors">Kayıt olun</a>
            </p>
        </div>
    </div>
</section>
<?php $this->stop() ?>
