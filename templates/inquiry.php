<?php $this->extend('layout.php') ?>

<?php $this->section('content') ?>
<section class="py-20">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="eyebrow">Sorgulama</span>
            <h2 class="mt-4 font-display text-4xl font-bold text-cream">Randevu Sorgula</h2>
            <p class="mt-4 text-muted">Telefon numaran ve onay kodunla randevunu görüntüle, ertele ya da iptal et.</p>
        </div>

        <div class="card p-8">
            <form id="inquiryForm" method="GET" novalidate>
                <div class="space-y-5">
                    <div>
                        <label for="inquiryPhone" class="label">Telefon Numaranız</label>
                        <input type="tel" id="inquiryPhone" name="phone" class="input" required placeholder="+90 5XX XXX XX XX">
                    </div>
                    <div>
                        <label for="inquiryCode" class="label">Onay Kodu</label>
                        <input type="text" id="inquiryCode" name="code" class="input" required placeholder="Örn: ABC123XYZ">
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full mt-8">Sorgula</button>
            </form>
            <div id="inquiryResult" class="mt-8 hidden" aria-live="polite"></div>

            <div id="inquiryActions" class="mt-6 hidden">
                <div class="grid sm:grid-cols-2 gap-4">
                    <button type="button" id="rescheduleBtn" class="btn-secondary w-full">Ertele</button>
                    <button type="button" id="cancelBtn" class="btn-danger w-full">İptal Et</button>
                </div>

                <div id="reschedulePanel" class="hidden mt-6 border-t border-line pt-6">
                    <label for="newStartTime" class="label">Yeni Tarih ve Saat</label>
                    <input type="datetime-local" id="newStartTime" class="input" step="900">
                    <div class="mt-4 flex gap-4">
                        <button type="button" id="rescheduleConfirmBtn" class="btn-primary flex-1">Ertelemeyi Onayla</button>
                        <button type="button" id="rescheduleCancelBtn" class="btn-secondary flex-1">Vazgeç</button>
                    </div>
                </div>

                <div id="cancelPanel" class="hidden mt-6 border-t border-line pt-6">
                    <p class="text-muted text-sm">Randevunu iptal etmek istediğine emin misin? Bu işlem geri alınamaz.</p>
                    <div class="mt-4 flex gap-4">
                        <button type="button" id="cancelConfirmBtn" class="btn-danger flex-1">İptali Onayla</button>
                        <button type="button" id="cancelBackBtn" class="btn-secondary flex-1">Vazgeç</button>
                    </div>
                </div>

                <div id="inquiryMessage" class="hidden mt-6" aria-live="polite"></div>
            </div>
        </div>
    </div>
</section>
<?php $this->stop() ?>
