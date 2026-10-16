<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="eyebrow">Randevu</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-bold text-cream">Randevu Al</h2>
            <p class="mt-4 text-muted">Hizmetini seç, ustanı belirle, saatini onayla.</p>
        </div>

        <div id="bookingSuccess" class="card hidden border-success/50 text-center p-10">
            <p class="font-display text-2xl font-bold text-success">Randevunuz Alındı</p>
            <p class="mt-3 text-muted">Onay kodunuz:</p>
            <p class="mt-2 font-display text-3xl font-bold text-gold tracking-widest" id="bookingCode"></p>
            <p class="mt-4 text-sm text-muted">Bu kodu saklayın; randevunuzu sorgulamak için kullanabilirsiniz.</p>
            <a href="/randevu-sorgula" class="btn-outline mt-8">Randevu Sorgula</a>
        </div>

        <div class="card" id="bookingCard">
            <div class="flex flex-wrap justify-center border-b border-line text-sm font-bold tracking-wider uppercase">
                <span class="px-5 py-4 border-b-2 border-gold -mb-px text-gold">1. Hizmet</span>
                <span class="px-5 py-4 border-b-2 border-transparent text-muted">2. Usta</span>
                <span class="px-5 py-4 border-b-2 border-transparent text-muted">3. Tarih &amp; Saat</span>
                <span class="px-5 py-4 border-b-2 border-transparent text-muted">4. Onay</span>
            </div>

            <form id="appointmentForm" action="/api/appointments" method="POST" class="p-8 space-y-8">
                <fieldset>
                    <legend class="label">Hizmetler</legend>
                    <div class="space-y-3" id="serviceList">
                        <p class="text-sm text-muted" id="serviceLoading">Hizmetler yükleniyor&hellip;</p>
                    </div>
                    <p class="mt-3 text-sm text-gold font-semibold" id="serviceSummary">Toplam: 0 dakika, 0 &#8378;</p>
                </fieldset>

                <div>
                    <label for="staffSelect" class="label">Berber</label>
                    <select id="staffSelect" class="input">
                        <option value="">Berber seçiniz...</option>
                    </select>
                    <div class="mt-2 text-sm text-muted" id="staffInfo"></div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="dateSelect" class="label">Tarih</label>
                        <input type="date" id="dateSelect" class="input">
                    </div>
                    <div>
                        <label for="timeSelect" class="label">Saat</label>
                        <select id="timeSelect" class="input">
                            <option value="">Önce bir tarih seçin</option>
                        </select>
                    </div>
                </div>
                <p class="text-sm text-muted" id="slotInfo">Müsait saatler seçildikçe gösterilecek.</p>

                <fieldset>
                    <legend class="label">Müşteri Bilgileri</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="customerName" class="label">Ad Soyad</label>
                            <input type="text" id="customerName" name="customer_name" class="input" required>
                        </div>
                        <div>
                            <label for="customerPhone" class="label">Telefon</label>
                            <input type="tel" id="customerPhone" name="customer_phone" class="input" required placeholder="+90 5XX XXX XX XX">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customerNote" class="label">Not <span class="normal-case font-normal">(isteğe bağlı)</span></label>
                            <textarea id="customerNote" name="customer_note" class="input" rows="3"></textarea>
                        </div>
                    </div>
                    <label class="flex items-start gap-3 mt-6 cursor-pointer">
                        <input type="checkbox" id="kvkkConsent" name="kvkk_consent" class="accent-gold h-4 w-4 mt-1" required>
                        <span class="text-sm text-muted">KVKK açıklama metnini okudum ve rıza ederim.</span>
                    </label>
                    <p class="mt-2 text-sm text-muted" id="kvkkInfo">Onay vermeden randevu oluşturamazsınız.</p>
                </fieldset>

                <p class="text-sm text-error hidden" id="formError"></p>

                <div class="flex items-center justify-between pt-6 border-t border-line">
                    <a href="/" class="btn-ghost">Ana Sayfa</a>
                    <button type="submit" id="submitBooking" class="btn-primary">Randevuyu Onayla</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?php $this->stop() ?>
