// Berber Randevu - front-end
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Yardımcılar ----------
    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', ...(options.headers || {}) },
            ...options
        });
        let json = null;
        try { json = await res.json(); } catch (e) {}
        return { ok: res.ok, status: res.status, json };
    }

    function getStoredToken() {
        try { return localStorage.getItem('br_token'); } catch (e) { return null; }
    }

    // ---------- Mobil menü ----------
    const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // ---------- Form doğrulama ----------
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-error');
                    let errorMessage = field.parentElement.querySelector('.error-message');
                    if (!errorMessage) {
                        errorMessage = document.createElement('p');
                        errorMessage.className = 'text-sm text-error mt-1 error-message';
                        errorMessage.textContent = 'Bu alan zorunludur';
                        field.parentElement.appendChild(errorMessage);
                    }
                } else {
                    field.classList.remove('border-error');
                    const errorMessage = field.parentElement.querySelector('.error-message');
                    if (errorMessage) errorMessage.remove();
                }
            });
            if (!isValid) e.preventDefault();
        });
    });

    document.addEventListener('input', function (e) {
        if (e.target.hasAttribute && e.target.hasAttribute('required')) {
            e.target.classList.remove('border-error');
            const errorMessage = e.target.parentElement.querySelector('.error-message');
            if (errorMessage) errorMessage.remove();
        }
    });

    // ---------- Admin login ----------
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const email = document.getElementById('adminEmail').value.trim();
            const password = document.getElementById('adminPassword').value;

            try {
                const { ok, json } = await apiFetch('/api/admin/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                if (ok && json && json.success && json.data && json.data.token) {
                    localStorage.setItem('br_token', json.data.token);
                    localStorage.setItem('br_user', JSON.stringify(json.data.user || {}));
                    window.location.href = '/admin';
                } else {
                    let errorMessage = adminLoginForm.querySelector('.error-message');
                    if (!errorMessage) {
                        errorMessage = document.createElement('p');
                        errorMessage.className = 'text-sm text-error mt-4 error-message';
                        adminLoginForm.appendChild(errorMessage);
                    }
                    errorMessage.textContent = (json && json.error) || 'Giriş başarısız. Bilgilerinizi kontrol edin.';
                }
            } catch (err) {
                alert('Bağlantı hatası. Lütfen tekrar deneyin.');
            }
        });
    }


    // ---------- Randevu formu (dinamik) ----------
    const bookingForm = document.getElementById('appointmentForm');
    if (bookingForm) {
        const serviceList = document.getElementById('serviceList');
        const staffSelect = document.getElementById('staffSelect');
        const dateSelect = document.getElementById('dateSelect');
        const timeSelect = document.getElementById('timeSelect');
        const serviceSummary = document.getElementById('serviceSummary');
        const slotInfo = document.getElementById('slotInfo');
        const staffInfo = document.getElementById('staffInfo');
        const formError = document.getElementById('formError');

        let services = [];
        let staff = [];

        // Bugünü minimum tarih yap
        const today = new Date();
        const pad = n => String(n).padStart(2, '0');
        dateSelect.min = today.getFullYear() + '-' + pad(today.getMonth() + 1) + '-' + pad(today.getDate());

        // Hizmetleri yükle
        apiFetch('/api/services').then(function (res) {
            if (!res.ok || !res.json || !res.json.success) {
                serviceList.innerHTML = '<p class="text-sm text-error">Hizmetler yüklenemedi. Lütfen sayfayı yenileyin.</p>';
                return;
            }
            services = res.json.data || [];
            serviceList.innerHTML = services.map(function (s) {
                return '<label class="flex items-center justify-between gap-3 border border-line hover:border-gold/60 cursor-pointer transition-colors p-4">'
                    + '<span class="flex items-center gap-3">'
                    + '<input type="checkbox" class="service-check accent-gold h-4 w-4" value="' + s.id + '">'
                    + '<span class="text-cream">' + s.name + ' <span class="text-muted">&mdash; ' + s.duration_minutes + ' dk, ' + s.price + ' &#8378;</span></span>'
                    + '</span></label>';
            }).join('');
            serviceList.querySelectorAll('.service-check').forEach(function (cb) {
                cb.addEventListener('change', updateSummary);
            });
        }).catch(function () {
            serviceList.innerHTML = '<p class="text-sm text-error">Hizmetler yüklenemedi. Lütfen sayfayı yenileyin.</p>';
        });

        function updateSummary() {
            let minutes = 0;
            let price = 0;
            serviceList.querySelectorAll('.service-check:checked').forEach(function (cb) {
                const s = services.find(x => x.id === cb.value);
                if (s) {
                    minutes += parseInt(s.duration_minutes, 10) || 0;
                    price += parseFloat(s.price) || 0;
                }
            });
            serviceSummary.textContent = 'Toplam: ' + minutes + ' dakika, ' + price + ' ₺';
            if (dateSelect.value && staffSelect.value) loadSlots();
        }

        // Ustaları yükle
        apiFetch('/api/staff').then(function (res) {
            if (!res.ok || !res.json || !res.json.success) return;
            staff = res.json.data || [];
            staff.forEach(function (m) {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.full_name;
                staffSelect.appendChild(opt);
            });
        }).catch(function () {});

        staffSelect.addEventListener('change', function () {
            const m = staff.find(x => x.id === staffSelect.value);
            staffInfo.textContent = m && m.bio ? m.bio : '';
            if (dateSelect.value) loadSlots();
        });

        dateSelect.addEventListener('change', function () {
            if (staffSelect.value) loadSlots();
        });

        function selectedDuration() {
            let minutes = 0;
            serviceList.querySelectorAll('.service-check:checked').forEach(function (cb) {
                const s = services.find(x => x.id === cb.value);
                if (s) minutes += parseInt(s.duration_minutes, 10) || 0;
            });
            return minutes;
        }

        let slotRequestId = 0;
        async function loadSlots() {
            const duration = selectedDuration();
            timeSelect.innerHTML = '<option value="">Yükleniyor...</option>';
            slotInfo.textContent = 'Müsait saatler getiriliyor...';

            if (duration <= 0) {
                timeSelect.innerHTML = '<option value="">Önce hizmet seçin</option>';
                slotInfo.textContent = 'Saatleri görmek için en az bir hizmet seçin.';
                return;
            }

            const requestId = ++slotRequestId;
            const url = '/api/available-slots?staff_id=' + encodeURIComponent(staffSelect.value)
                + '&date=' + encodeURIComponent(dateSelect.value)
                + '&service_duration=' + duration;

            try {
                const res = await apiFetch(url);
                if (requestId !== slotRequestId) return;

                if (!res.ok || !res.json || !res.json.success) {
                    timeSelect.innerHTML = '<option value="">Müsait saat yok</option>';
                    slotInfo.textContent = (res.json && res.json.error) || 'Müsait saatler alınamadı.';
                    return;
                }

                const slots = (res.json.data && res.json.data.slots) || [];
                if (slots.length === 0) {
                    timeSelect.innerHTML = '<option value="">Müsait saat yok</option>';
                    slotInfo.textContent = 'Bu tarihte uygun saat görünmüyor. Başka gün deneyin.';
                    return;
                }

                timeSelect.innerHTML = '<option value="">Saat seçin...</option>'
                    + slots.map(function (s) {
                        return '<option value="' + s.start + '">' + s.start + ' – ' + s.end + '</option>';
                    }).join('');
                slotInfo.textContent = slots.length + ' müsait saat bulundu.';
            } catch (err) {
                if (requestId !== slotRequestId) return;
                timeSelect.innerHTML = '<option value="">Hata oluştu</option>';
                slotInfo.textContent = 'Müsait saatler alınamadı.';
            }
        }

        function showFormError(message) {
            formError.textContent = message;
            formError.classList.remove('hidden');
        }

        // Gönderim
        bookingForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            formError.classList.add('hidden');

            const checkedServices = Array.from(serviceList.querySelectorAll('.service-check:checked')).map(function (cb) { return cb.value; });
            if (checkedServices.length === 0) {
                showFormError('Lütfen en az bir hizmet seçin.');
                return;
            }
            if (!staffSelect.value) { showFormError('Lütfen bir berber seçin.'); return; }
            if (!dateSelect.value || !timeSelect.value) { showFormError('Lütfen tarih ve saat seçin.'); return; }

            const customerName = document.getElementById('customerName').value.trim();
            const customerPhone = document.getElementById('customerPhone').value.trim();
            if (!customerName || !customerPhone) {
                showFormError('Ad soyad ve telefon alanları zorunludur.');
                return;
            }
            if (!document.getElementById('kvkkConsent').checked) {
                showFormError('KVKK onayı gereklidir.');
                return;
            }

            const submitBtn = document.getElementById('submitBooking');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Gönderiliyor...';

            const payload = {
                services: checkedServices,
                staff_id: staffSelect.value,
                start_time: dateSelect.value + ' ' + timeSelect.value + ':00',
                customer_name: customerName,
                customer_phone: customerPhone,
                customer_note: document.getElementById('customerNote').value.trim() || null,
                kvkk_consent: true,
                idempotency_key: (crypto.randomUUID ? crypto.randomUUID() : Date.now() + '-' + Math.random().toString(36).slice(2))
            };

            try {
                const res = await apiFetch('/api/appointments', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (res.ok && res.json && res.json.success) {
                    document.getElementById('bookingCode').textContent = res.json.data.confirmation_code || '';
                    document.getElementById('bookingCard').classList.add('hidden');
                    document.getElementById('bookingSuccess').classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    showFormError((res.json && res.json.error) || 'Randevu oluşturulamadı. Lütfen tekrar deneyin.');
                }
            } catch (err) {
                showFormError('Bağlantı hatası. Lütfen tekrar deneyin.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Randevuyu Onayla';
            }
        });
    }




    // ---------- Randevu sorgulama (sonuc sayfada, API'ye yonlenme yok) ----------
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        const phoneInput = document.getElementById('inquiryPhone');
        const codeInput = document.getElementById('inquiryCode');
        const resultBox = document.getElementById('inquiryResult');
        const actionsBox = document.getElementById('inquiryActions');
        const reschedulePanel = document.getElementById('reschedulePanel');
        const cancelPanel = document.getElementById('cancelPanel');
        const messageBox = document.getElementById('inquiryMessage');

        let current = { phone: '', code: '', status: '' };

        const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        function fmtDateTime(v) {
            if (!v) return '-';
            const d = new Date(String(v).replace(' ', 'T'));
            if (isNaN(d.getTime())) return esc(v);
            return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' })
                + ' ' + d.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        }

        const statusLabels = { booked: 'Planlandı', completed: 'Tamamlandı', cancelled: 'İptal Edildi', no_show: 'Gelmedi' };

        function showMessage(kind, text) {
            messageBox.classList.remove('hidden');
            messageBox.innerHTML = '<p class="text-sm ' + (kind === 'error' ? 'text-error' : 'text-gold') + '">' + esc(text) + '</p>';
        }

        function hidePanels() {
            reschedulePanel.classList.add('hidden');
            cancelPanel.classList.add('hidden');
            messageBox.classList.add('hidden');
            messageBox.innerHTML = '';
        }

        function renderAppointment(a) {
            const services = (a.services || []).map(function (s) {
                const name = s.service_name || s.name || '';
                return '<li class="flex items-center justify-between gap-3 py-2 border-b border-line last:border-0">'
                    + '<span class="text-cream">' + esc(name) + '</span>'
                    + '<span class="text-muted text-sm">' + esc(s.duration_minutes || '') + ' dk &mdash; ' + esc(s.price || '') + ' &#8378;</span></li>';
            }).join('');

            resultBox.classList.remove('hidden');
            resultBox.innerHTML = '<div class="border border-gold/30 p-6">'
                + '<div class="flex items-center justify-between gap-3 flex-wrap">'
                + '<p class="text-xs font-bold tracking-[0.25em] uppercase text-gold">Randevu Bulundu</p>'
                + '<span class="text-xs font-bold tracking-widest uppercase text-cream">' + esc(statusLabels[a.status] || a.status || '') + '</span>'
                + '</div>'
                + '<h3 class="mt-3 font-display text-2xl font-bold text-cream">' + esc(a.customer_name || '') + '</h3>'
                + '<p class="mt-1 text-sm text-muted">' + esc(a.customer_phone || '') + ' &middot; Kod: <span class="text-gold font-semibold">' + esc(a.confirmation_code || '') + '</span></p>'
                + '<p class="mt-4 text-cream font-semibold">' + fmtDateTime(a.start_time) + '</p>'
                + (a.customer_note ? '<p class="mt-2 text-sm text-muted">Not: ' + esc(a.customer_note) + '</p>' : '')
                + (services ? '<ul class="mt-4">' + services + '</ul>' : '')
                + '</div>';
        }

        inquiryForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            hidePanels();
            actionsBox.classList.add('hidden');
            resultBox.classList.remove('hidden');
            resultBox.innerHTML = '<p class="text-sm text-muted">Randevu aranıyor...</p>';

            const phone = phoneInput.value.trim();
            const code = codeInput.value.trim().toUpperCase();
            if (!phone || !code) {
                resultBox.innerHTML = '<p class="text-sm text-error">Telefon ve onay kodu zorunludur.</p>';
                return;
            }
            codeInput.value = code;
            current = { phone: phone, code: code, status: '' };

            try {
                const res = await apiFetch('/api/appointments/inquiry?phone=' + encodeURIComponent(phone) + '&code=' + encodeURIComponent(code));
                if (!res.ok || !res.json || !res.json.success) {
                    resultBox.innerHTML = '<p class="text-sm text-error">' + esc((res.json && res.json.error) || 'Randevu bulunamadı.') + '</p>';
                    return;
                }
                const a = res.json.data.appointment || {};
                current.status = String(a.status || '');
                renderAppointment(a);
                if (current.status === 'booked') actionsBox.classList.remove('hidden');
            } catch (err) {
                resultBox.innerHTML = '<p class="text-sm text-error">Bağlantı hatası. Lütfen tekrar deneyin.</p>';
            }
        });

        document.getElementById('rescheduleBtn').addEventListener('click', function () {
            reschedulePanel.classList.toggle('hidden');
            cancelPanel.classList.add('hidden');
            messageBox.classList.add('hidden');
        });

        document.getElementById('cancelBtn').addEventListener('click', function () {
            cancelPanel.classList.toggle('hidden');
            reschedulePanel.classList.add('hidden');
            messageBox.classList.add('hidden');
        });

        document.getElementById('rescheduleCancelBtn').addEventListener('click', function () {
            reschedulePanel.classList.add('hidden');
        });

        document.getElementById('cancelBackBtn').addEventListener('click', function () {
            cancelPanel.classList.add('hidden');
        });
        document.getElementById('rescheduleConfirmBtn').addEventListener('click', async function () {
            const raw = document.getElementById('newStartTime').value;
            if (!raw) { showMessage('error', 'Yeni tarih ve saat seçin.'); return; }
            const iso = raw.length === 16 ? raw + ':00' : raw;
            try {
                const res = await apiFetch('/api/appointments/reschedule', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone: current.phone, confirmation_code: current.code, new_start_time: iso })
                });
                if (!res.ok || !res.json || !res.json.success) {
                    showMessage('error', (res.json && res.json.error) || 'Erteleme başarısız.');
                    return;
                }
                showMessage('ok', 'Randevu ertelendi: ' + fmtDateTime(res.json.data.start_time) + ' – ' + fmtDateTime(res.json.data.end_time));
                reschedulePanel.classList.add('hidden');
            } catch (err) {
                showMessage('error', 'Bağlantı hatası. Lütfen tekrar deneyin.');
            }
        });

        document.getElementById('cancelConfirmBtn').addEventListener('click', async function () {
            try {
                const res = await apiFetch('/api/appointments/cancel', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone: current.phone, confirmation_code: current.code })
                });
                if (!res.ok || !res.json || !res.json.success) {
                    showMessage('error', (res.json && res.json.error) || 'İptal başarısız.');
                    return;
                }
                current.status = 'cancelled';
                showMessage('ok', 'Randevu iptal edildi.');
                cancelPanel.classList.add('hidden');
                document.getElementById('rescheduleBtn').style.display = 'none';
                document.getElementById('cancelBtn').style.display = 'none';
            } catch (err) {
                showMessage('error', 'Bağlantı hatası. Lütfen tekrar deneyin.');
            }
        });
    }

    // ---------- Admin dashboard ----------
    const adminGuard = document.querySelector('[data-admin-guard]');
    if (adminGuard) {
        const token = getStoredToken();
        if (!token) {
            window.location.replace('/admin/giris');
            return;
        }

        const authHeaders = { 'Authorization': 'Bearer ' + token };

        try {
            const user = JSON.parse(localStorage.getItem('br_user') || '{}');
            if (user.email) document.getElementById('adminEmailLabel').textContent = user.email;
        } catch (e) {}

        const dateEl = document.getElementById('todayDate');
        if (dateEl) {
            dateEl.textContent = new Date().toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        document.getElementById('adminLogoutBtn').addEventListener('click', function () {
            apiFetch('/api/auth/logout', { method: 'POST', headers: authHeaders })
                .finally(function () {
                    try { localStorage.removeItem('br_token'); localStorage.removeItem('br_user'); } catch (e) {}
                    window.location.href = '/admin/giris';
                });
        });

        // İstatistikler
        apiFetch('/api/admin/stats', { headers: authHeaders }).then(function (res) {
            if (res.ok && res.json && res.json.success) {
                document.getElementById('statToday').textContent = res.json.data.today;
                document.getElementById('statWeek').textContent = res.json.data.week;
                document.getElementById('statStaff').textContent = res.json.data.active_staff;
                document.getElementById('statServices').textContent = res.json.data.active_services;
            }
        }).catch(function () {});

        // ---------- Hizmet & Usta yonetimi ----------
        const adminMsg = document.getElementById('adminMessage');
        const escHtml = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        function adminNotify(kind, text) {
            if (!adminMsg) return;
            adminMsg.classList.remove('hidden');
            adminMsg.innerHTML = '<p class="text-sm ' + (kind === 'error' ? 'text-error' : 'text-gold') + '">' + escHtml(text) + '</p>';
        }

        const tabServices = document.getElementById('tabServices');
        const tabStaff = document.getElementById('tabStaff');
        if (tabServices && tabStaff) {
            tabServices.addEventListener('click', function () {
                document.getElementById('panelServices').classList.remove('hidden');
                document.getElementById('panelStaff').classList.add('hidden');
            });
            tabStaff.addEventListener('click', function () {
                document.getElementById('panelStaff').classList.remove('hidden');
                document.getElementById('panelServices').classList.add('hidden');
            });
        }
        const servicesTable = document.getElementById('servicesTable');
        let serviceCache = [];
        async function loadAdminServices() {
            if (!servicesTable) return;
            try {
                const res = await apiFetch('/api/admin/services', { headers: authHeaders });
                if (!res.ok || !res.json || !res.json.success) {
                    servicesTable.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-muted">Hizmetler yüklenemedi.</td></tr>';
                    return;
                }
                serviceCache = res.json.data || [];
                if (serviceCache.length === 0) {
                    servicesTable.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-muted">Kayıtlı hizmet yok.</td></tr>';
                    return;
                }
                servicesTable.innerHTML = serviceCache.map(function (s) {
                    return '<tr class="border-b border-line last:border-0">'
                        + '<td class="px-4 py-3 text-cream font-semibold">' + escHtml(s.name) + '<br><span class="text-muted font-normal text-xs">' + escHtml(s.category || '') + '</span></td>'
                        + '<td class="px-4 py-3 text-muted">' + escHtml(s.duration_minutes) + ' dk</td>'
                        + '<td class="px-4 py-3 text-muted">' + escHtml(s.price) + ' &#8378;</td>'
                        + '<td class="px-4 py-3">' + (s.is_active ? '<span class="text-xs font-bold tracking-widest uppercase text-gold">Aktif</span>' : '<span class="text-xs font-bold tracking-widest uppercase text-muted">Pasif</span>') + '</td>'
                        + '<td class="px-4 py-3 text-right whitespace-nowrap">'
                        + '<button type="button" class="btn-ghost" data-svc-edit="' + escHtml(s.id) + '">Düzenle</button> '
                        + '<button type="button" class="btn-ghost" data-svc-toggle="' + escHtml(s.id) + '">' + (s.is_active ? 'Pasife Al' : 'Aktifleştir') + '</button> '
                        + '<button type="button" class="btn-ghost" data-svc-del="' + escHtml(s.id) + '">Sil</button>'
                        + '</td></tr>';
                }).join('');
            } catch (err) {
                servicesTable.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-muted">Hizmetler yüklenemedi.</td></tr>';
            }
        }
        loadAdminServices();

        function resetServiceForm() {
            const f = document.getElementById('serviceForm');
            if (f) f.reset();
            document.getElementById('svcId').value = '';
            document.getElementById('svcSubmit').textContent = 'Ekle';
            document.getElementById('svcCancel').classList.add('hidden');
        }

        const serviceForm = document.getElementById('serviceForm');
        if (serviceForm) {
            serviceForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const id = document.getElementById('svcId').value;
                const body = {
                    name: document.getElementById('svcName').value.trim(),
                    duration_minutes: parseInt(document.getElementById('svcDuration').value, 10),
                    price: parseFloat(document.getElementById('svcPrice').value),
                    category: document.getElementById('svcCategory').value.trim() || 'Genel'
                };
                const url = id ? '/api/admin/services/' + encodeURIComponent(id) : '/api/admin/services';
                try {
                    const res = await apiFetch(url, { method: id ? 'PATCH' : 'POST', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
                    if (!res.ok || !res.json || !res.json.success) {
                        adminNotify('error', (res.json && res.json.error) || 'Kaydetme başarısız.');
                        return;
                    }
                    adminNotify('ok', id ? 'Hizmet güncellendi.' : 'Hizmet eklendi.');
                    resetServiceForm();
                    loadAdminServices();
                } catch (err) {
                    adminNotify('error', 'Bağlantı hatası.');
                }
            });
            document.getElementById('svcCancel').addEventListener('click', resetServiceForm);
            servicesTable.addEventListener('click', async function (e) {
                const t = e.target;
                const g = k => (t.getAttribute ? t.getAttribute(k) : null);
                const editId = g('data-svc-edit'), toggleId = g('data-svc-toggle'), delId = g('data-svc-del');
                if (editId) {
                    const s = serviceCache.find(x => String(x.id) === editId);
                    if (!s) return;
                    document.getElementById('svcId').value = s.id;
                    document.getElementById('svcName').value = s.name || '';
                    document.getElementById('svcDuration').value = s.duration_minutes || 30;
                    document.getElementById('svcPrice').value = s.price || 0;
                    document.getElementById('svcCategory').value = s.category || 'Genel';
                    document.getElementById('svcSubmit').textContent = 'Güncelle';
                    document.getElementById('svcCancel').classList.remove('hidden');
                    document.getElementById('yonetim').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
                if (toggleId) {
                    const s = serviceCache.find(x => String(x.id) === toggleId);
                    const res = await apiFetch('/api/admin/services/' + encodeURIComponent(toggleId), { method: 'PATCH', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify({ is_active: !(s && s.is_active) }) }).catch(() => null);
                    if (!res || !res.ok || !res.json || !res.json.success) { adminNotify('error', (res && res.json && res.json.error) || 'Durum değiştirilemedi.'); return; }
                    adminNotify('ok', 'Hizmet durumu güncellendi.');
                    loadAdminServices();
                    return;
                }
                if (delId) {
                    if (!window.confirm('Bu hizmet silinsin mi? Randevularda kullanılıyorsa pasife alınır.')) return;
                    const res = await apiFetch('/api/admin/services/' + encodeURIComponent(delId), { method: 'DELETE', headers: authHeaders }).catch(() => null);
                    if (!res || !res.ok || !res.json || !res.json.success) { adminNotify('error', (res && res.json && res.json.error) || 'Silme başarısız.'); return; }
                    adminNotify('ok', (res.json.data && res.json.data.deactivated) ? 'Hizmet randevularda kullanıldığı için pasife alındı.' : 'Hizmet silindi.');
                    loadAdminServices();
                }
            });
        }
        const staffTable = document.getElementById('staffTable');
        let staffCache = [];
        async function loadAdminStaff() {
            if (!staffTable) return;
            const res = await apiFetch('/api/admin/staff', { headers: authHeaders }).catch(() => null);
            if (!res || !res.ok || !res.json || !res.json.success) {
                staffTable.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-muted">Ustalar yüklenemedi.</td></tr>';
                return;
            }
            staffCache = res.json.data || [];
            if (staffCache.length === 0) {
                staffTable.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-muted">Kayıtlı usta yok.</td></tr>';
                return;
            }
            staffTable.innerHTML = staffCache.map(function (m) {
                return '<tr class="border-b border-line last:border-0">'
                    + '<td class="px-4 py-3 text-cream font-semibold">' + escHtml(m.full_name) + '</td>'
                    + '<td class="px-4 py-3 text-muted text-xs max-w-xs truncate">' + escHtml(m.bio || '-') + '</td>'
                    + '<td class="px-4 py-3">' + (m.is_active ? '<span class="text-xs font-bold tracking-widest uppercase text-gold">Aktif</span>' : '<span class="text-xs font-bold tracking-widest uppercase text-muted">Pasif</span>') + '</td>'
                    + '<td class="px-4 py-3 text-right whitespace-nowrap">'
                    + '<button type="button" class="btn-ghost" data-staff-edit="' + escHtml(m.id) + '">Düzenle</button> '
                    + '<button type="button" class="btn-ghost" data-staff-toggle="' + escHtml(m.id) + '">' + (m.is_active ? 'Pasife Al' : 'Aktifleştir') + '</button>'
                    + '</td></tr>';
            }).join('');
        }
        loadAdminStaff();

        const staffCreateForm = document.getElementById('staffCreateForm');
        if (staffCreateForm) {
            staffCreateForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const body = {
                    full_name: document.getElementById('newStaffName').value.trim(),
                    bio: document.getElementById('newStaffBio').value.trim()
                };
                if (!body.full_name) { adminNotify('error', 'Usta adı zorunludur.'); return; }
                const res = await apiFetch('/api/admin/staff', { method: 'POST', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify(body) }).catch(() => null);
                if (!res || !res.ok || !res.json || !res.json.success) { adminNotify('error', (res && res.json && res.json.error) || 'Usta eklenemedi.'); return; }
                adminNotify('ok', 'Yeni usta eklendi: ' + body.full_name);
                staffCreateForm.reset();
                loadAdminStaff();
                loadStaffFilter();
            });
        }

        const staffForm = document.getElementById('staffForm');
        if (staffForm) {
            staffForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const id = document.getElementById('staffId').value;
                const body = { full_name: document.getElementById('staffName').value.trim(), bio: document.getElementById('staffBio').value.trim() };
                const res = await apiFetch('/api/admin/staff/' + encodeURIComponent(id), { method: 'PATCH', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify(body) }).catch(() => null);
                if (!res || !res.ok || !res.json || !res.json.success) { adminNotify('error', (res && res.json && res.json.error) || 'Kaydetme başarısız.'); return; }
                adminNotify('ok', 'Usta güncellendi.');
                staffForm.classList.add('hidden');
                loadAdminStaff();
            });
            document.getElementById('staffCancel').addEventListener('click', function () {
                staffForm.classList.add('hidden');
            });
            staffTable.addEventListener('click', async function (e) {
                const t = e.target;
                const g = k => (t.getAttribute ? t.getAttribute(k) : null);
                const editId = g('data-staff-edit'), toggleId = g('data-staff-toggle');
                if (editId) {
                    const m = staffCache.find(x => String(x.id) === editId);
                    if (!m) return;
                    document.getElementById('staffId').value = m.id;
                    document.getElementById('staffName').value = m.full_name || '';
                    document.getElementById('staffBio').value = m.bio || '';
                    staffForm.classList.remove('hidden');
                    document.getElementById('yonetim').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
                if (toggleId) {
                    const m = staffCache.find(x => String(x.id) === toggleId);
                    const res = await apiFetch('/api/admin/staff/' + encodeURIComponent(toggleId), { method: 'PATCH', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify({ is_active: !(m && m.is_active) }) }).catch(() => null);
                    if (!res || !res.ok || !res.json || !res.json.success) { adminNotify('error', (res && res.json && res.json.error) || 'Durum değiştirilemedi.'); return; }
                    adminNotify('ok', 'Usta durumu güncellendi.');
                    loadAdminStaff();
                }
            });
        }

        // Randevular: tarih araligi + usta/durum filtresi
        const statusLabel = { booked: 'Planlandı', completed: 'Tamamlandı', cancelled: 'İptal', no_show: 'Gelmedi' };
        const fltFrom = document.getElementById('fltFrom');
        const fltTo = document.getElementById('fltTo');
        const fltStaff = document.getElementById('fltStaff');
        const fltStatus = document.getElementById('fltStatus');

        const pad2 = n => String(n).padStart(2, '0');
        const toISO = d => d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        function weekBounds(base) {
            const day = (base.getDay() + 6) % 7;
            const mon = new Date(base); mon.setDate(base.getDate() - day);
            const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
            return [toISO(mon), toISO(sun)];
        }

        function applyQuickRange(kind) {
            const now = new Date();
            if (kind === 'today') {
                fltFrom.value = toISO(now); fltTo.value = toISO(now);
            } else if (kind === 'tomorrow') {
                const t = new Date(now); t.setDate(now.getDate() + 1);
                fltFrom.value = toISO(t); fltTo.value = toISO(t);
            } else if (kind === 'week') {
                const b = weekBounds(now);
                fltFrom.value = b[0]; fltTo.value = b[1];
            } else if (kind === 'month') {
                fltFrom.value = now.getFullYear() + '-' + pad2(now.getMonth() + 1) + '-01';
                const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                fltTo.value = toISO(last);
            }
            loadAppointments();
        }

        document.querySelectorAll('[data-range]').forEach(function (btn) {
            btn.addEventListener('click', function () { applyQuickRange(btn.getAttribute('data-range')); });
        });
        const fltApply = document.getElementById('fltApply');
        if (fltApply) fltApply.addEventListener('click', loadAppointments);

        // Usta filtresi secenekleri (yeni usta eklenince tazelenir)
        async function loadStaffFilter() {
            if (!fltStaff) return;
            const res = await apiFetch('/api/admin/staff', { headers: authHeaders }).catch(() => null);
            if (!res || !res.ok || !res.json || !res.json.success) return;
            const keep = fltStaff.value;
            fltStaff.innerHTML = '<option value="">Tümü</option>' + (res.json.data || []).map(function (m) {
                return '<option value="' + escHtml(m.id) + '">' + escHtml(m.full_name || 'Usta') + '</option>';
            }).join('');
            fltStaff.value = keep;
        }

        const apptStatusOptions = [['booked', 'Planlandı'], ['completed', 'Tamamlandı'], ['cancelled', 'İptal'], ['no_show', 'Gelmedi']];

        async function loadAppointments() {
            const tbody = document.getElementById('todayAppointments');
            if (!tbody) return;
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-muted">Randevular yükleniyor&hellip;</td></tr>';

            let url = '/api/admin/appointments?from=' + encodeURIComponent(fltFrom.value || toISO(new Date()))
                + '&to=' + encodeURIComponent(fltTo.value || fltFrom.value || toISO(new Date()));
            if (fltStaff && fltStaff.value) url += '&staff_id=' + encodeURIComponent(fltStaff.value);
            if (fltStatus && fltStatus.value) url += '&status=' + encodeURIComponent(fltStatus.value);

            const res = await apiFetch(url, { headers: authHeaders }).catch(() => null);
            if (!res || !res.ok || !res.json || !res.json.success) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-muted">' + escHtml((res && res.json && res.json.error) || 'Randevular yüklenemedi.') + '</td></tr>';
                return;
            }

            const rows = res.json.data.appointments || [];
            if (rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-muted">Seçili aralıkta randevu yok.</td></tr>';
                return;
            }

            tbody.innerHTML = rows.map(function (a) {
                const raw = String(a.start_time || '');
                const datePart = raw.slice(0, 10);
                const start = raw.slice(11, 16);
                const d = new Date(datePart + 'T12:00:00');
                const dateLabel = isNaN(d.getTime()) ? datePart : d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', weekday: 'short' });
                const services = (a.services || []).join(', ');
                const opts = apptStatusOptions.map(function (o) {
                    return '<option value="' + o[0] + '"' + (a.status === o[0] ? ' selected' : '') + '>' + o[1] + '</option>';
                }).join('');
                return '<tr class="border-b border-line last:border-0">'
                    + '<td class="px-6 py-4 text-cream whitespace-nowrap">' + escHtml(dateLabel) + '</td>'
                    + '<td class="px-6 py-4 text-gold font-semibold">' + escHtml(start) + '</td>'
                    + '<td class="px-6 py-4 text-cream">' + escHtml(a.customer_name || '-') + '<br><span class="text-muted text-xs">' + escHtml(a.customer_phone || '') + '</span></td>'
                    + '<td class="px-6 py-4 text-muted">' + escHtml(services || '-') + '</td>'
                    + '<td class="px-6 py-4 text-muted">' + escHtml(a.staff_name || '-') + '</td>'
                    + '<td class="px-6 py-4"><span class="text-xs font-bold tracking-widest uppercase">'
                    + escHtml(statusLabel[a.status] || a.status) + '</span></td>'
                    + '<td class="px-6 py-4 text-right whitespace-nowrap">'
                    + '<select class="input" data-appt-status="' + escHtml(a.id) + '" data-current="' + escHtml(a.status || '') + '" style="max-width:11rem">' + opts + '</select> '
                    + '<button type="button" class="btn-ghost" data-appt-save="' + escHtml(a.id) + '">Kaydet</button>'
                    + '</td></tr>';
            }).join('');
        }

        document.getElementById('todayAppointments').addEventListener('click', async function (e) {
            const t = e.target;
            const saveId = t.getAttribute ? t.getAttribute('data-appt-save') : null;
            if (!saveId) return;
            const sel = document.querySelector('select[data-appt-status="' + saveId + '"]');
            if (!sel) return;
            if (sel.value === sel.getAttribute('data-current')) {
                adminNotify('error', 'Durum değişmedi.');
                return;
            }
            t.disabled = true;
            const res = await apiFetch('/api/admin/appointments/' + encodeURIComponent(saveId), { method: 'PATCH', headers: { ...authHeaders, 'Content-Type': 'application/json' }, body: JSON.stringify({ status: sel.value }) }).catch(() => null);
            t.disabled = false;
            if (!res || !res.ok || !res.json || !res.json.success) {
                adminNotify('error', (res && res.json && res.json.error) || 'Durum güncellenemedi.');
                return;
            }
            adminNotify('ok', 'Randevu durumu güncellendi.');
            loadAppointments();
        });

        if (fltFrom && !fltFrom.value) {
            fltFrom.value = toISO(new Date());
            fltTo.value = toISO(new Date());
        }
        loadStaffFilter();
        loadAppointments();
    }
});
