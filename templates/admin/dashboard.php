<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" data-admin-guard>
    <!-- Başlık -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
        <div>
            <span class="eyebrow">Yönetim Paneli</span>
            <h1 class="mt-3 font-display text-3xl md:text-4xl font-bold text-cream">Hoş geldiniz</h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-muted" id="adminEmailLabel"></span>
            <button type="button" id="adminLogoutBtn" class="btn-ghost">Çıkış Yap</button>
        </div>
    </div>

    <!-- İstatistik kartları -->
    <div class="grid gap-px sm:grid-cols-2 lg:grid-cols-4 bg-line border border-line">
        <div class="bg-coal p-6">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-muted">Bugünkü Randevu</p>
            <p class="mt-3 font-display text-4xl font-bold text-gold" id="statToday">&mdash;</p>
        </div>
        <div class="bg-coal p-6">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-muted">Bu Hafta</p>
            <p class="mt-3 font-display text-4xl font-bold text-gold" id="statWeek">&mdash;</p>
        </div>
        <div class="bg-coal p-6">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-muted">Aktif Usta</p>
            <p class="mt-3 font-display text-4xl font-bold text-gold" id="statStaff">&mdash;</p>
        </div>
        <div class="bg-coal p-6">
            <p class="text-xs font-bold tracking-[0.2em] uppercase text-muted">Toplam Hizmet</p>
            <p class="mt-3 font-display text-4xl font-bold text-gold" id="statServices">&mdash;</p>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3 mt-10">
        <!-- Randevular (tarih araligi + filtre) -->
        <section class="card lg:col-span-2">
            <div class="card-header flex items-center justify-between gap-3 flex-wrap">
                <h2 class="font-display text-xl font-semibold text-cream">Randevular</h2>
                <span class="text-xs tracking-[0.2em] uppercase text-muted" id="todayDate"></span>
            </div>
            <div class="px-6 py-4 border-b border-line grid gap-3 md:grid-cols-4">
                <div>
                    <label class="label" for="fltFrom">Başlangıç</label>
                    <input type="date" id="fltFrom" class="input">
                </div>
                <div>
                    <label class="label" for="fltTo">Bitiş</label>
                    <input type="date" id="fltTo" class="input">
                </div>
                <div>
                    <label class="label" for="fltStaff">Usta</label>
                    <select id="fltStaff" class="input">
                        <option value="">Tümü</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="fltStatus">Durum</label>
                    <select id="fltStatus" class="input">
                        <option value="">Tümü</option>
                        <option value="booked">Planlandı</option>
                        <option value="completed">Tamamlandı</option>
                        <option value="cancelled">İptal</option>
                        <option value="no_show">Gelmedi</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex gap-2 flex-wrap">
                    <button type="button" class="btn-ghost" data-range="today">Bugün</button>
                    <button type="button" class="btn-ghost" data-range="tomorrow">Yarın</button>
                    <button type="button" class="btn-ghost" data-range="week">Bu Hafta</button>
                    <button type="button" class="btn-ghost" data-range="month">Bu Ay</button>
                    <button type="button" class="btn-primary" id="fltApply">Listele</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line text-left text-xs tracking-[0.2em] uppercase text-muted">
                            <th class="px-6 py-3 font-bold">Tarih</th>
                            <th class="px-6 py-3 font-bold">Saat</th>
                            <th class="px-6 py-3 font-bold">Müşteri</th>
                            <th class="px-6 py-3 font-bold">Hizmet</th>
                            <th class="px-6 py-3 font-bold">Usta</th>
                            <th class="px-6 py-3 font-bold">Durum</th>
                            <th class="px-6 py-3 font-bold text-right">İşlem</th>
                        </tr>
                    </thead>
                    <tbody id="todayAppointments">
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-muted">
                                Randevular yükleniyor&hellip;
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Hızlı işlemler -->
        <aside class="space-y-8">
            <section class="card">
                <div class="card-header">
                    <h2 class="font-display text-xl font-semibold text-cream">Hızlı İşlemler</h2>
                </div>
                <div class="card-body space-y-3">
                    <a href="/randevu" class="btn-outline w-full">Yeni Randevu Oluştur</a>
                    <a href="#yonetim" class="btn-ghost w-full" id="manageLink">Hizmet &amp; Usta Yönetimi</a>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="font-display text-xl font-semibold text-cream">Duyurular</h2>
                </div>
                <div class="card-body">
                    <p class="text-sm text-muted leading-relaxed">
                        Aktif duyuru bulunmuyor. Yeni duyurular için veritabanındaki
                        <code class="text-gold">announcements</code> tablosunu kullanabilirsiniz.
                    </p>
                </div>
            </section>
        </aside>
    </div>

    <!-- Hizmet & Usta Yonetimi -->
    <section class="card mt-10" id="yonetim">
        <div class="card-header flex items-center gap-6 flex-wrap">
            <h2 class="font-display text-xl font-semibold text-cream">Yönetim</h2>
            <div class="flex gap-2">
                <button type="button" id="tabServices" class="btn-ghost">Hizmetler</button>
                <button type="button" id="tabStaff" class="btn-ghost">Ustalar</button>
            </div>
        </div>
        <div class="card-body">
            <div id="adminMessage" class="hidden mb-4" aria-live="polite"></div>

            <!-- Hizmetler sekmesi -->
            <div id="panelServices">
                <form id="serviceForm" class="grid gap-4 md:grid-cols-6 mb-6">
                    <input type="hidden" id="svcId">
                    <div class="md:col-span-2">
                        <label class="label" for="svcName">Hizmet Adı</label>
                        <input type="text" id="svcName" class="input" required placeholder="Örn: Sakal Tıraşı">
                    </div>
                    <div>
                        <label class="label" for="svcDuration">Süre (dk)</label>
                        <input type="number" id="svcDuration" class="input" required min="5" max="480" value="30">
                    </div>
                    <div>
                        <label class="label" for="svcPrice">Fiyat (₺)</label>
                        <input type="number" id="svcPrice" class="input" required min="0" step="0.01" value="150">
                    </div>
                    <div>
                        <label class="label" for="svcCategory">Kategori</label>
                        <input type="text" id="svcCategory" class="input" value="Genel">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary flex-1" id="svcSubmit">Ekle</button>
                        <button type="button" class="btn-secondary hidden" id="svcCancel">Vazgeç</button>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line text-left text-xs tracking-[0.2em] uppercase text-muted">
                                <th class="px-4 py-3 font-bold">Hizmet</th>
                                <th class="px-4 py-3 font-bold">Süre</th>
                                <th class="px-4 py-3 font-bold">Fiyat</th>
                                <th class="px-4 py-3 font-bold">Durum</th>
                                <th class="px-4 py-3 font-bold text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTable">
                            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">Yükleniyor&hellip;</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ustalar sekmesi -->
            <div id="panelStaff" class="hidden">
                <form id="staffCreateForm" class="grid gap-4 md:grid-cols-3 mb-6">
                    <div>
                        <label class="label" for="newStaffName">Ad Soyad</label>
                        <input type="text" id="newStaffName" class="input" required placeholder="Örn: Usta Can">
                    </div>
                    <div class="md:col-span-2">
                        <label class="label" for="newStaffBio">Biyografi</label>
                        <input type="text" id="newStaffBio" class="input" placeholder="Uzmanlık, deneyim...">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="btn-primary w-full">Yeni Usta Ekle</button>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line text-left text-xs tracking-[0.2em] uppercase text-muted">
                                <th class="px-4 py-3 font-bold">Usta</th>
                                <th class="px-4 py-3 font-bold">Biyografi</th>
                                <th class="px-4 py-3 font-bold">Durum</th>
                                <th class="px-4 py-3 font-bold text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="staffTable">
                            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">Yükleniyor&hellip;</td></tr>
                        </tbody>
                    </table>
                </div>
                <form id="staffForm" class="hidden mt-6 border-t border-line pt-6 grid gap-4 md:grid-cols-3">
                    <input type="hidden" id="staffId">
                    <div>
                        <label class="label" for="staffName">Ad Soyad</label>
                        <input type="text" id="staffName" class="input" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label" for="staffBio">Biyografi</label>
                        <input type="text" id="staffBio" class="input" placeholder="Uzmanlık, deneyim...">
                    </div>
                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit" class="btn-primary flex-1">Kaydet</button>
                        <button type="button" class="btn-secondary flex-1" id="staffCancel">Vazgeç</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
// Basit koruma: Supabase Auth oturum token'ı yoksa giriş sayfasına yönlendir.
(function () {
    var token = null;
    try { token = localStorage.getItem('br_token'); } catch (e) {}
    if (!token) {
        window.location.replace('/admin/giris');
        return;
    }
    try {
        var user = JSON.parse(localStorage.getItem('br_user') || '{}');
        if (user.email) {
            document.getElementById('adminEmailLabel').textContent = user.email;
        }
    } catch (e) {}
    var el = document.getElementById('todayDate');
    if (el) {
        el.textContent = new Date().toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });
    }
    document.getElementById('adminLogoutBtn').addEventListener('click', function () {
        fetch('/api/auth/logout', { method: 'POST' })
            .finally(function () {
                try { localStorage.removeItem('br_token'); localStorage.removeItem('br_user'); } catch (e) {}
                window.location.href = '/admin/giris';
            });
    });
})();
</script>
<?php $this->stop() ?>
