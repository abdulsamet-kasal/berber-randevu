-- Supabase seed data for berber-randevu
-- Insert example data for development

-- Business settings
insert into public.business_settings (
    id, business_name, address, phone, map_embed, default_working_hours,
    cancellation_policy_hours, booking_lead_time_minutes, max_booking_days
) values (
    '00000000-0000-0000-0000-000000000001',
    'Berber Randevu',
    'Example Street 123, Istanbul',
    '+90 212 555 12 34',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d28.9795!3d41.0123!2m3!1f0!2f0!3f0!3m2!1i1024!2i768" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '{"monday": {"start": "09:00", "end": "19:00"}}',
    24,
    30,
    30
);

-- Services
insert into public.services (id, name, description, duration_minutes, price, category, is_active, sort_order) values
('00000000-0000-0000-0000-000000000002', 'Saç Kesimi', 'Klasik berber saç kesimi', 30, 150.00, 'Kesim', true, 1),
('00000000-0000-0000-0000-000000000003', 'Tırnak Bakımı', 'Profesyonel tırnak düzenleme ve bakım', 20, 80.00, 'Bakım', true, 2),
('00000000-0000-0000-0000-000000000004', 'Sakal Şekillendirme', 'Sakal şekillendirme ve bakım', 25, 120.00, 'Sakal', true, 3),
('00000000-0000-0000-0000-000000000005', 'Kombine Paket', 'Saç kesimi + tırnak + sakal', 60, 300.00, 'Paket', true, 4);

-- Staff (example barbers)
-- NOTE: profiles.id references auth.users(id). Create login users first via
-- Supabase Auth Admin API, then use their real UUIDs below.
-- Example dev users (see docs/ARCHITECTURE.md): yonetici@, ahmet@, mehmet@, ali@berber-randevu.local
-- First create profiles for staff (assuming auth.users ids)
-- We'll use placeholder uuids for profiles; in real scenario these would link to auth.users
insert into public.profiles (id, role, full_name, avatar_url) values
('00000000-0000-0000-0000-000000000010', 'admin', 'Yönetici', '/assets/images/admin-avatar.png'),
('00000000-0000-0000-0000-000000000011', 'staff', 'Usta Ahmet', '/assets/images/staff1.jpg'),
('00000000-0000-0000-0000-000000000012', 'staff', 'Usta Mehmet', '/assets/images/staff2.jpg'),
('00000000-0000-0000-0000-000000000013', 'staff', 'Usta Ali', '/assets/images/staff3.jpg')
on conflict (id) do nothing;

insert into public.staff (id, profile_id, bio, is_active) values
('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000011', '10 yıllık deneyimli berber, klasik kesimler konusunda uzman.', true),
('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000012', 'Sakal ve bıyık uzmanı, modern stiller konusunda yaratıcı.', true),
('00000000-0000-0000-0000-000000000023', '00000000-0000-0000-0000-000000000013', 'Genç ve dinamik berber, genç müşteriler arasında popüler.', true);

-- Staff services
insert into public.staff_services (staff_id, service_id) values
('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000002'),
('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000003'),
('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000004'),
('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000002'),
('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000004'),
('00000000-0000-0000-0000-000000000023', '00000000-0000-0000-0000-000000000002'),
('00000000-0000-0000-0000-000000000023', '00000000-0000-0000-0000-000000000003');

-- Working hours (example: Monday-Friday 9-19, Saturday 9-17, Sunday off)
insert into public.working_hours (id, staff_id, day_of_week, start_time, end_time, is_active) values
-- Staff 1 (Ahmet)
('00000000-0000-0000-0000-000000000031', '00000000-0000-0000-0000-000000000021', 1, '09:00:00', '19:00:00', true), -- Monday
('00000000-0000-0000-0000-000000000032', '00000000-0000-0000-0000-000000000021', 2, '09:00:00', '19:00:00', true), -- Tuesday
('00000000-0000-0000-0000-000000000033', '00000000-0000-0000-0000-000000000021', 3, '09:00:00', '19:00:00', true), -- Wednesday
('00000000-0000-0000-0000-000000000034', '00000000-0000-0000-0000-000000000021', 4, '09:00:00', '19:00:00', true), -- Thursday
('00000000-0000-0000-0000-000000000035', '00000000-0000-0000-0000-000000000021', 5, '09:00:00', '19:00:00', true), -- Friday
('00000000-0000-0000-0000-000000000036', '00000000-0000-0000-0000-000000000021', 6, '09:00:00', '17:00:00', true), -- Saturday
-- Staff 2 (Mehmet)
('00000000-0000-0000-0000-000000000041', '00000000-0000-0000-0000-000000000022', 1, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000042', '00000000-0000-0000-0000-000000000022', 2, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000043', '00000000-0000-0000-0000-000000000022', 3, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000044', '00000000-0000-0000-0000-000000000022', 4, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000045', '00000000-0000-0000-0000-000000000022', 5, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000046', '00000000-0000-0000-0000-000000000022', 6, '09:00:00', '17:00:00', true),
-- Staff 3 (Ali)
('00000000-0000-0000-0000-000000000051', '00000000-0000-0000-0000-000000000023', 1, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000052', '00000000-0000-0000-0000-000000000023', 2, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000053', '00000000-0000-0000-0000-000000000023', 3, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000054', '00000000-0000-0000-0000-000000000023', 4, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000055', '00000000-0000-0000-0000-000000000023', 5, '09:00:00', '19:00:00', true),
('00000000-0000-0000-0000-000000000056', '00000000-0000-0000-0000-000000000023', 6, '09:00:00', '17:00:00', true);

-- Time off examples
insert into public.time_off (id, staff_id, start_date, end_date, reason) values
('00000000-0000-0000-0000-000000000061', '00000000-0000-0000-0000-000000000021', '2026-12-25', '2026-12-26', 'Noel tatili'),
('00000000-0000-0000-0000-000000000062', '00000000-0000-0000-0000-000000000022', '2026-11-10', null, 'Özel izin');

-- Gallery items
insert into public.gallery_items (id, category, title, image_url, is_published, sort_order) values
('00000000-0000-0000-0000-000000000071', 'before-after', 'Saç Kesimi Dönüşümü', '/assets/images/gallery1.jpg', true, 1),
('00000000-0000-0000-0000-000000000072', 'before-after', 'Sakal Şekillendirme', '/assets/images/gallery2.jpg', true, 2),
('00000000-0000-0000-0000-000000000073', 'salon-interior', 'Salon İç Mekan', '/assets/images/gallery3.jpg', true, 3);

-- Announcements
insert into public.announcements (id, title, content, starts_at, ends_at, is_active) values
('00000000-0000-0000-0000-000000000081', 'Yaz İndirimi', 'Tüm hizmetlerde %10 indirim!', '2026-06-01 00:00:00', '2026-08-31 23:59:59', true);

-- Example customer (misafir)
insert into public.customers (id, name, phone, email, kvkk_consent, kvkk_consent_at) values
('00000000-0000-0000-0000-000000000090', 'Ali Yılmaz', '+90 555 123 45 67', 'ali@example.com', true, now())
on conflict (id) do nothing;

-- Example appointment
insert into public.appointments (id, staff_id, customer_id, start_time, end_time, status, confirmation_code, customer_note, internal_note, idempotency_key) values
('00000000-0000-0000-0000-000000000100', '00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000090', '2026-10-20 10:00:00+03', '2026-10-20 10:30:00+03', 'booked', 'ABC123XYZ', 'Lütfen biraz önce gelin', 'Müşteri saat 10:00 geldi', '00000000-0000-0000-0000-000000000999');

-- Appointment services snapshot
insert into public.appointment_services (id, appointment_id, service_id, service_name, duration_minutes, price) values
('00000000-0000-0000-0000-000000000110', '00000000-0000-0000-0000-000000000100', '00000000-0000-0000-0000-000000000002', 'Saç Kesimi', 30, 150.00);

-- Reviews
insert into public.reviews (id, appointment_id, rating, comment, is_published) values
('00000000-0000-0000-0000-000000000120', '00000000-0000-0000-0000-000000000100', 5, 'Harika bir berber deneyimi, kesinlikle tavsiye ederim!', true);
