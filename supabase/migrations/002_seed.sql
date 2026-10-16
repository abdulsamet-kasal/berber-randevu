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
)
on conflict (id) do nothing;

-- Services
insert into public.services (id, name, description, duration_minutes, price, category, is_active, sort_order) values
('00000000-0000-0000-0000-000000000002', 'Saç Kesimi', 'Klasik berber saç kesimi', 30, 150.00, 'Kesim', true, 1),
('00000000-0000-0000-0000-000000000003', 'Tırnak Bakımı', 'Profesyonel tırnak düzenleme ve bakım', 20, 80.00, 'Bakım', true, 2),
('00000000-0000-0000-0000-000000000004', 'Sakal Şekillendirme', 'Sakal şekillendirme ve bakım', 25, 120.00, 'Sakal', true, 3),
('00000000-0000-0000-0000-000000000005', 'Kombine Paket', 'Saç kesimi + tırnak + sakal', 60, 300.00, 'Paket', true, 4);

-- Gallery items
insert into public.gallery_items (id, category, title, image_url, is_published, sort_order) values
('00000000-0000-0000-0000-000000000071', 'before-after', 'Saç Kesimi Dönüşümü', '/assets/images/gallery1.jpg', true, 1),
('00000000-0000-0000-0000-000000000072', 'before-after', 'Sakal Şekillendirme', '/assets/images/gallery2.jpg', true, 2),
('00000000-0000-0000-0000-000000000073', 'salon-interior', 'Salon İç Mekan', '/assets/images/gallery3.jpg', true, 3);

-- Announcements
insert into public.announcements (id, title, content, starts_at, ends_at, is_active) values
('00000000-0000-0000-0000-000000000081', 'Yaz İndirimi', 'Tüm hizmetlerde %10 indirim!', '2026-06-01 00:00:00', '2026-08-31 23:59:59', true);

-- Example customer (misafir) - no auth dependency
insert into public.customers (id, name, phone, email, kvkk_consent, kvkk_consent_at) values
('00000000-0000-0000-0000-000000000090', 'Ali Yılmaz', '+90 555 123 45 67', 'ali@example.com', true, now())
on conflict (id) do nothing;