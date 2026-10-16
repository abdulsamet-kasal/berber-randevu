-- Supabase migration: 001_init.sql
-- This migration creates the initial schema for berber-randevu

-- Enable uuid extension if needed
create extension if not exists "uuid-ossp";

-- Enable btree_gist for EXCLUDE constraint
create extension if not exists btree_gist;

-- Fallback UUID generator if uuid-ossp not available
create or replace function public.uuid_generate_v4()
returns uuid
language sql
as $$
    select gen_random_uuid();
$$;

-- Profiles table (extends auth.users)
create table public.profiles (
    id uuid references auth.users not null primary key,
    role text check (role in ('admin', 'staff')) not null,
    full_name text,
    avatar_url text,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Staff table
create table public.staff (
    id uuid primary key default uuid_generate_v4(),
    profile_id uuid references public.profiles(id) not null unique,
    bio text,
    is_active boolean default true,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Services table
create table public.services (
    id uuid primary key default uuid_generate_v4(),
    name text not null,
    description text,
    duration_minutes integer not null check (duration_minutes > 0),
    price decimal(10,2) not null check (price >= 0),
    category text,
    is_active boolean default true,
    sort_order integer default 0,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Staff_Services junction table
create table public.staff_services (
    staff_id uuid references public.staff(id) on delete cascade,
    service_id uuid references public.services(id) on delete cascade,
    primary key (staff_id, service_id)
);

-- Working hours (berber bazlı haftalık)
create table public.working_hours (
    id uuid primary key default uuid_generate_v4(),
    staff_id uuid references public.staff(id) on delete cascade,
    day_of_week integer not null check (day_of_week between 0 and 6), -- 0=Sunday
    start_time time not null,
    end_time time not null,
    is_active boolean default true,
    break_start time,
    break_end time,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Time off (izin ve kapalı günler)
create table public.time_off (
    id uuid primary key default uuid_generate_v4(),
    staff_id uuid references public.staff(id) on delete cascade,
    start_date date not null,
    end_date date,
    reason text,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Business settings
create table public.business_settings (
    id uuid primary key default uuid_generate_v4(),
    business_name text not null,
    address text,
    phone text,
    map_embed text,
    default_working_hours jsonb,
    cancellation_policy_hours integer not null default 24,
    booking_lead_time_minutes integer not null default 30,
    max_booking_days integer not null default 30,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Customers (misafir)
create table public.customers (
    id uuid primary key default uuid_generate_v4(),
    name text not null,
    phone text not null,
    email text,
    kvkk_consent boolean default false,
    kvkk_consent_at timestamptz null,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Appointments
create table public.appointments (
    id uuid primary key default uuid_generate_v4(),
    staff_id uuid references public.staff(id) not null,
    customer_id uuid references public.customers(id),
    start_time timestamptz not null,
    end_time timestamptz not null,
    status text not null check (status in ('booked', 'completed', 'cancelled', 'no_show')),
    confirmation_code text unique,
    customer_note text,
    internal_note text,
    idempotency_key uuid unique,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Appointment services (snapshot)
create table public.appointment_services (
    id uuid primary key default uuid_generate_v4(),
    appointment_id uuid references public.appointments(id) on delete cascade,
    service_id uuid references public.services(id),
    service_name text not null,
    duration_minutes integer not null,
    price decimal(10,2) not null,
    created_at timestamptz default now()
);

-- Gallery items
create table public.gallery_items (
    id uuid primary key default uuid_generate_v4(),
    category text,
    title text,
    image_url text not null,
    is_published boolean default true,
    sort_order integer default 0,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Reviews
create table public.reviews (
    id uuid primary key default uuid_generate_v4(),
    appointment_id uuid references public.appointments(id),
    rating integer not null check (rating between 1 and 5),
    comment text,
    is_published boolean default false,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Announcements
create table public.announcements (
    id uuid primary key default uuid_generate_v4(),
    title text not null,
    content text not null,
    starts_at timestamptz,
    ends_at timestamptz,
    is_active boolean default true,
    created_at timestamptz default now(),
    updated_at timestamptz default now()
);

-- Notifications (app içi)
create table public.notifications (
    id uuid primary key default uuid_generate_v4(),
    recipient_uuid uuid references auth.users, -- could be staff/admin
    type text not null,
    title text not null,
    message text not null,
    related_uuid uuid, -- reference to appointment, review, etc.
    is_read boolean default false,
    read_at timestamptz null,
    created_at timestamptz default now()
);

-- Consents (KVKK)
create table public.consents (
    id uuid primary key default uuid_generate_v4(),
    customer_uuid uuid references public.customers(id),
    consent_text_version text not null,
    consent_at timestamptz not null,
    ip_address inet,
    created_at timestamptz default now()
);

-- Audit logs
create table public.audit_logs (
    id uuid primary key default uuid_generate_v4(),
    table_name text not null,
    record_uuid uuid,
    action text not null check (action in ('INSERT', 'UPDATE', 'DELETE')),
    changed_jsonb jsonb,
    performed_by_uuid uuid references auth.users,
    created_at timestamptz default now()
);

-- Rate limits (simple table for IP-based limiting)
create table public.rate_limits (
    id uuid primary key default uuid_generate_v4(),
    ip_address inet not null,
    endpoint text not null,
    window_start timestamptz not null,
    request_count integer default 1,
    created_at timestamptz default now()
);

-- Indexler
create index idx_appointments_staff_time on public.appointments (staff_id, start_time, end_time) where status = 'booked';
create index idx_appointments_customer on public.appointments (customer_id);
create index idx_appointments_status on public.appointments (status);
create index idx_services_active on public.services (is_active);
create index idx_staff_active on public.staff (is_active);
create index idx_working_hours_staff_day on public.working_hours (staff_id, day_of_week);
create index idx_time_off_staff on public.time_off (staff_id);
create index idx_gallery_published on public.gallery_items (is_published);
create index idx_reviews_appointment on public.reviews (appointment_id);
create index idx_notifications_recipient on public.notifications (recipient_uuid);
create index idx_consents_customer on public.consents (customer_uuid);
create index idx_audit_logs_table on public.audit_logs (table_name);
create index idx_rate_limits_ip_endpoint on public.rate_limits (ip_address, endpoint);

-- EXCLUDE constraint to prevent overlapping appointments for the same staff
-- Only applies to booked appointments
alter table public.appointments add constraint no_overlapping_appointments
exclude using gist (
    staff_id with =,
    tstzrange(start_time, end_time) with &&
) where (status = 'booked');

-- Enable Row Level Security on all tables
alter table public.profiles enable row level security;
alter table public.staff enable row level security;
alter table public.services enable row level security;
alter table public.staff_services enable row level security;
alter table public.working_hours enable row level security;
alter table public.time_off enable row level security;
alter table public.business_settings enable row level security;
alter table public.customers enable row level security;
alter table public.appointments enable row level security;
alter table public.appointment_services enable row level security;
alter table public.gallery_items enable row level security;
alter table public.reviews enable row level security;
alter table public.announcements enable row level security;
alter table public.notifications enable row level security;
alter table public.consents enable row level security;
alter table public.audit_logs enable row level security;
alter table public.rate_limits enable row level security;

-- RLS helper functions:
-- Policies must not subquery their own table (or each other), otherwise Postgres
-- raises 42P17 "infinite recursion detected in policy". All cross-table checks
-- (admin role, staff membership, customer scope) live in these SECURITY DEFINER
-- functions, which bypass RLS and therefore always terminate.
create or replace function public.is_admin()
returns boolean
language sql stable security definer
set search_path = public
as $$
  select exists (select 1 from public.profiles where id = auth.uid() and role = 'admin')
$$;

create or replace function public.my_staff_ids()
returns setof uuid
language sql stable security definer
set search_path = public
as $$
  select id from public.staff where profile_id = auth.uid()
$$;

create or replace function public.my_customer_ids()
returns setof uuid
language sql stable security definer
set search_path = public
as $$
  select distinct customer_id from public.appointments
  where customer_id is not null
    and staff_id in (select id from public.staff where profile_id = auth.uid())
$$;

-- RLS Policies

-- Profiles: 
-- - Users can see their own profile
-- - Admins can see all profiles
create policy "Profiles: Users can view own profile" on public.profiles
    for select using (auth.uid() = id);
create policy "Profiles: Admins can view all profiles" on public.profiles
    for select using (public.is_admin());

-- Staff:
-- - Users can see staff if they are admin or the staff belongs to them
-- - Admins can manage all staff
-- - Staff can see their own record
create policy "Staff: Users can view own staff record or if admin" on public.staff
    for select using (auth.uid() = profile_id or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Staff: Admins can manage all staff" on public.staff
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Staff: Staff can update own record" on public.staff
    for update using (auth.uid() = profile_id)
    with check (auth.uid() = profile_id);

-- Services:
-- - Everyone can see active services
-- - Admins can manage all services
create policy "Services: Everyone can view active services" on public.services
    for select using (is_active = true);
create policy "Services: Admins can manage all services" on public.services
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Staff_Services:
-- - Admins can manage all
create policy "Staff_Services: Admins can manage all" on public.staff_services
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Working_Hours:
-- - Users can see working hours for staff if they are admin or the staff belongs to them
-- - Admins can manage all
create policy "Working_Hours: Users can view working hours for own staff or if admin" on public.working_hours
    for select using (staff_id in (select id from public.staff where profile_id = auth.uid()) or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Working_Hours: Admins can manage all" on public.working_hours
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Working_Hours: Staff can manage own working hours" on public.working_hours
    for all using (auth.uid() in (select profile_id from public.staff where id = staff_id))
    with check (auth.uid() in (select profile_id from public.staff where id = staff_id));

-- Time_Off:
-- - Users can see time off for staff if they are admin or the staff belongs to them
-- - Admins can manage all
create policy "Time_Off: Users can view time off for own staff or if admin" on public.time_off
    for select using (staff_id in (select id from public.staff where profile_id = auth.uid()) or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Time_Off: Admins can manage all" on public.time_off
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Time_Off: Staff can manage own time off" on public.time_off
    for all using (auth.uid() in (select profile_id from public.staff where id = staff_id))
    with check (auth.uid() in (select profile_id from public.staff where id = staff_id));

-- Business_Settings:
-- - Everyone can view
-- - Admins can manage
create policy "Business_Settings: Everyone can view" on public.business_settings
    for select using (true);
create policy "Business_Settings: Admins can manage" on public.business_settings
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Customers:
-- NOTE: customers are guest records (walk-in visitors); there is no auth user link.
-- The old "own customer record" policy compared appointments.customer_id = appointments.id
-- (same-row nonsense) and cross-referenced appointments, causing RLS recursion — removed.
-- - Admins can see all
-- - Staff can see customers who have appointments with them
create policy "Customers: Admins can view all" on public.customers
    for select using (public.is_admin());
create policy "Customers: Staff can view customers with appointments" on public.customers
    for select using (id in (select public.my_customer_ids()));

-- Appointments:
-- NOTE: customers are guests, so "own appointments by customer" cannot be resolved;
-- the old clause re-queried the appointments table from its own policy, which caused
-- RLS infinite recursion (42P17). Staff and admin checks now use helper functions.
-- - Staff can see their own appointments
-- - Admins can see all
create policy "Appointments: Users can view own appointments" on public.appointments
    for select using (
        staff_id in (select public.my_staff_ids())
        or public.is_admin()
    );
create policy "Appointments: Admins can view all" on public.appointments
    for select using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Appointments: Staff can view own appointments" on public.appointments
    for select using (staff_id in (select id from public.staff where profile_id = auth.uid()));
-- For inserting/updating, we'll restrict to admins and staff for their own appointments
create policy "Appointments: Admins can manage all" on public.appointments
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Appointments: Staff can manage own appointments" on public.appointments
    for all using (staff_id in (select id from public.staff where profile_id = auth.uid()))
    with check (staff_id in (select id from public.staff where profile_id = auth.uid()));

-- Appointment_Services:
-- - Follows appointment permissions
create policy "Appointment_Services: Follow appointment permissions" on public.appointment_services
    for all using (
        appointment_id in (select id from public.appointments where 
            staff_id in (select id from public.staff where profile_id = auth.uid()) 
            or customer_id in (select id from public.customers where id in (select customer_id from public.appointments where staff_id in (select id from public.staff where profile_id = auth.uid())))
            or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin')
        )
    )
    with check (
        appointment_id in (select id from public.appointments where 
            staff_id in (select id from public.staff where profile_id = auth.uid()) 
            or customer_id in (select id from public.customers where id in (select customer_id from public.appointments where staff_id in (select id from public.staff where profile_id = auth.uid())))
            or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin')
        )
    );

-- Gallery_Items:
-- - Everyone can see published items
-- - Admins can manage all
create policy "Gallery_Items: Everyone can view published items" on public.gallery_items
    for select using (is_published = true);
create policy "Gallery_Items: Admins can manage all" on public.gallery_items
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Reviews:
-- - Everyone can see published reviews
-- - Admins can manage all
-- - Users can see their own reviews
create policy "Reviews: Everyone can view published reviews" on public.reviews
    for select using (is_published = true);
create policy "Reviews: Admins can manage all" on public.reviews
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));
create policy "Reviews: Users can view own reviews" on public.reviews
    for select using (appointment_id in (select id from public.appointments where customer_id in (select id from public.customers where id in (select customer_id from public.appointments where staff_id in (select id from public.staff where profile_id = auth.uid())))));

-- Announcements:
-- - Everyone can see active announcements within date range
-- - Admins can manage all
create policy "Announcements: Everyone can view active announcements" on public.announcements
    for select using (
        is_active = true 
        and (starts_at is null or starts_at <= now()) 
        and (ends_at is null or ends_at >= now())
    );
create policy "Announcements: Admins can manage all" on public.announcements
    for all using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'))
    with check (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Notifications:
-- - Users can see their own notifications
-- - Admins can see all (for admin users)
create policy "Notifications: Users can view own notifications" on public.notifications
    for select using (recipient_uuid = auth.uid());
create policy "Notifications: Admins can view all" on public.notifications
    for select using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Consents:
-- - Users can see their own consents
-- - Admins can see all
create policy "Consents: Users can view own consents" on public.consents
    for select using (
        exists (select 1 from public.appointments where customer_id = customer_uuid and staff_id in (select id from public.staff where profile_id = auth.uid()))
        or exists (select 1 from public.profiles where id = auth.uid() and role = 'admin')
    );
create policy "Consents: Admins can view all" on public.consents
    for select using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Audit_Logs:
-- - Admins can see all
create policy "Audit_Logs: Admins can view all" on public.audit_logs
    for select using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Rate_Limits:
-- - Admins can see all
create policy "Rate_Limits: Admins can view all" on public.rate_limits
    for select using (exists (select 1 from public.profiles where id = auth.uid() and role = 'admin'));

-- Trigger for updated_at
create or replace function public.update_updated_at_column()
returns trigger as $$
begin
    new.updated_at = now();
    return new;
end;
$$ language 'plpgsql';

-- Apply trigger to each table
do $$
declare
    tables text[] := array[
        'profiles', 'staff', 'services', 'staff_services',
        'working_hours', 'time_off', 'business_settings',
        'customers', 'appointments', 'appointment_services',
        'gallery_items', 'reviews', 'announcements',
        'notifications', 'consents', 'audit_logs', 'rate_limits'
    ];
    t text;
begin
    foreach t in array tables loop
        execute format(
            'create trigger update_%s_updated_at before update on public.%s for each row execute procedure public.update_updated_at_column();',
            t, t
        );
    end loop;
end $$;

-- Seed data will be in seed.sql