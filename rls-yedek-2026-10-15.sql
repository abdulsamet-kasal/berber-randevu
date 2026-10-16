-- RLS YEDEK - 15 Ekim 2026 (berber-randevu, proje: ihedhtyjcegvmglrfghv)
-- Degisiklik oncesi tum RLS politikalarinin birebir yedegi.
-- Geri yuklemek icin: supabase SQL Editor'de veya psql ile calistirin.

drop policy if exists "Announcements: Admins can manage all" on public.announcements;
create policy "Announcements: Admins can manage all" on public.announcements for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Announcements: Everyone can view active announcements" on public.announcements;
create policy "Announcements: Everyone can view active announcements" on public.announcements for SELECT to public
  using (((is_active = true) AND ((starts_at IS NULL) OR (starts_at <= now())) AND ((ends_at IS NULL) OR (ends_at >= now()))));

drop policy if exists "Appointment_Services: Follow appointment permissions" on public.appointment_services;
create policy "Appointment_Services: Follow appointment permissions" on public.appointment_services for ALL to public
  using ((appointment_id IN ( SELECT appointments.id
   FROM appointments
  WHERE ((appointments.staff_id IN ( SELECT staff.id
           FROM staff
          WHERE (staff.profile_id = auth.uid()))) OR (appointments.customer_id IN ( SELECT customers.id
           FROM customers
          WHERE (customers.id IN ( SELECT appointments_1.customer_id
                   FROM appointments appointments_1
                  WHERE (appointments_1.staff_id IN ( SELECT staff.id
                           FROM staff
                          WHERE (staff.profile_id = auth.uid()))))))) OR (EXISTS ( SELECT 1
           FROM profiles
          WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))))))
  with check ((appointment_id IN ( SELECT appointments.id
   FROM appointments
  WHERE ((appointments.staff_id IN ( SELECT staff.id
           FROM staff
          WHERE (staff.profile_id = auth.uid()))) OR (appointments.customer_id IN ( SELECT customers.id
           FROM customers
          WHERE (customers.id IN ( SELECT appointments_1.customer_id
                   FROM appointments appointments_1
                  WHERE (appointments_1.staff_id IN ( SELECT staff.id
                           FROM staff
                          WHERE (staff.profile_id = auth.uid()))))))) OR (EXISTS ( SELECT 1
           FROM profiles
          WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))))));

drop policy if exists "Appointments: Admins can manage all" on public.appointments;
create policy "Appointments: Admins can manage all" on public.appointments for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Appointments: Admins can view all" on public.appointments;
create policy "Appointments: Admins can view all" on public.appointments for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Appointments: Staff can manage own appointments" on public.appointments;
create policy "Appointments: Staff can manage own appointments" on public.appointments for ALL to public
  using ((staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))))
  with check ((staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))));

drop policy if exists "Appointments: Staff can view own appointments" on public.appointments;
create policy "Appointments: Staff can view own appointments" on public.appointments for SELECT to public
  using ((staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))));

drop policy if exists "Appointments: Users can view own appointments" on public.appointments;
create policy "Appointments: Users can view own appointments" on public.appointments for SELECT to public
  using (((customer_id IN ( SELECT customers.id
   FROM customers
  WHERE (customers.id IN ( SELECT appointments_1.customer_id
           FROM appointments appointments_1
          WHERE (appointments_1.staff_id IN ( SELECT staff.id
                   FROM staff
                  WHERE (staff.profile_id = auth.uid()))))))) OR (staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))) OR (EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))));

drop policy if exists "Audit_Logs: Admins can view all" on public.audit_logs;
create policy "Audit_Logs: Admins can view all" on public.audit_logs for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Business_Settings: Admins can manage" on public.business_settings;
create policy "Business_Settings: Admins can manage" on public.business_settings for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Business_Settings: Everyone can view" on public.business_settings;
create policy "Business_Settings: Everyone can view" on public.business_settings for SELECT to public
  using (true);

drop policy if exists "Consents: Admins can view all" on public.consents;
create policy "Consents: Admins can view all" on public.consents for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Consents: Users can view own consents" on public.consents;
create policy "Consents: Users can view own consents" on public.consents for SELECT to public
  using (((EXISTS ( SELECT 1
   FROM appointments
  WHERE ((appointments.customer_id = consents.customer_uuid) AND (appointments.staff_id IN ( SELECT staff.id
           FROM staff
          WHERE (staff.profile_id = auth.uid())))))) OR (EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))));

drop policy if exists "Customers: Admins can view all" on public.customers;
create policy "Customers: Admins can view all" on public.customers for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Customers: Staff can view customers with appointments" on public.customers;
create policy "Customers: Staff can view customers with appointments" on public.customers for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM appointments
  WHERE ((appointments.customer_id = appointments.id) AND (appointments.staff_id IN ( SELECT staff.id
           FROM staff
          WHERE (staff.profile_id = auth.uid())))))));

drop policy if exists "Customers: Users can view own customer record" on public.customers;
create policy "Customers: Users can view own customer record" on public.customers for SELECT to public
  using (((auth.uid() IN ( SELECT staff.profile_id
   FROM staff
  WHERE (staff.id IN ( SELECT appointments.staff_id
           FROM appointments
          WHERE (appointments.customer_id = appointments.id))))) OR (id IN ( SELECT appointments.customer_id
   FROM appointments
  WHERE (appointments.staff_id IN ( SELECT staff.id
           FROM staff
          WHERE (staff.profile_id = auth.uid())))))));

drop policy if exists "Gallery_Items: Admins can manage all" on public.gallery_items;
create policy "Gallery_Items: Admins can manage all" on public.gallery_items for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Gallery_Items: Everyone can view published items" on public.gallery_items;
create policy "Gallery_Items: Everyone can view published items" on public.gallery_items for SELECT to public
  using ((is_published = true));

drop policy if exists "Notifications: Admins can view all" on public.notifications;
create policy "Notifications: Admins can view all" on public.notifications for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Notifications: Users can view own notifications" on public.notifications;
create policy "Notifications: Users can view own notifications" on public.notifications for SELECT to public
  using ((recipient_uuid = auth.uid()));

drop policy if exists "Profiles: Admins can view all profiles" on public.profiles;
create policy "Profiles: Admins can view all profiles" on public.profiles for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles profiles_1
  WHERE ((profiles_1.id = auth.uid()) AND (profiles_1.role = 'admin'::text)))));

drop policy if exists "Profiles: Users can view own profile" on public.profiles;
create policy "Profiles: Users can view own profile" on public.profiles for SELECT to public
  using ((auth.uid() = id));

drop policy if exists "Rate_Limits: Admins can view all" on public.rate_limits;
create policy "Rate_Limits: Admins can view all" on public.rate_limits for SELECT to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Reviews: Admins can manage all" on public.reviews;
create policy "Reviews: Admins can manage all" on public.reviews for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Reviews: Everyone can view published reviews" on public.reviews;
create policy "Reviews: Everyone can view published reviews" on public.reviews for SELECT to public
  using ((is_published = true));

drop policy if exists "Reviews: Users can view own reviews" on public.reviews;
create policy "Reviews: Users can view own reviews" on public.reviews for SELECT to public
  using ((appointment_id IN ( SELECT appointments.id
   FROM appointments
  WHERE (appointments.customer_id IN ( SELECT customers.id
           FROM customers
          WHERE (customers.id IN ( SELECT appointments_1.customer_id
                   FROM appointments appointments_1
                  WHERE (appointments_1.staff_id IN ( SELECT staff.id
                           FROM staff
                          WHERE (staff.profile_id = auth.uid()))))))))));

drop policy if exists "Services: Admins can manage all services" on public.services;
create policy "Services: Admins can manage all services" on public.services for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Services: Everyone can view active services" on public.services;
create policy "Services: Everyone can view active services" on public.services for SELECT to public
  using ((is_active = true));

drop policy if exists "Staff: Admins can manage all staff" on public.staff;
create policy "Staff: Admins can manage all staff" on public.staff for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Staff: Staff can update own record" on public.staff;
create policy "Staff: Staff can update own record" on public.staff for UPDATE to public
  using ((auth.uid() = profile_id))
  with check ((auth.uid() = profile_id));

drop policy if exists "Staff: Users can view own staff record or if admin" on public.staff;
create policy "Staff: Users can view own staff record or if admin" on public.staff for SELECT to public
  using (((auth.uid() = profile_id) OR (EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))));

drop policy if exists "Staff_Services: Admins can manage all" on public.staff_services;
create policy "Staff_Services: Admins can manage all" on public.staff_services for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Time_Off: Admins can manage all" on public.time_off;
create policy "Time_Off: Admins can manage all" on public.time_off for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Time_Off: Staff can manage own time off" on public.time_off;
create policy "Time_Off: Staff can manage own time off" on public.time_off for ALL to public
  using ((auth.uid() IN ( SELECT staff.profile_id
   FROM staff
  WHERE (staff.id = time_off.staff_id))))
  with check ((auth.uid() IN ( SELECT staff.profile_id
   FROM staff
  WHERE (staff.id = time_off.staff_id))));

drop policy if exists "Time_Off: Users can view time off for own staff or if admin" on public.time_off;
create policy "Time_Off: Users can view time off for own staff or if admin" on public.time_off for SELECT to public
  using (((staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))) OR (EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))));

drop policy if exists "Working_Hours: Admins can manage all" on public.working_hours;
create policy "Working_Hours: Admins can manage all" on public.working_hours for ALL to public
  using ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))))
  with check ((EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text)))));

drop policy if exists "Working_Hours: Staff can manage own working hours" on public.working_hours;
create policy "Working_Hours: Staff can manage own working hours" on public.working_hours for ALL to public
  using ((auth.uid() IN ( SELECT staff.profile_id
   FROM staff
  WHERE (staff.id = working_hours.staff_id))))
  with check ((auth.uid() IN ( SELECT staff.profile_id
   FROM staff
  WHERE (staff.id = working_hours.staff_id))));

drop policy if exists "Working_Hours: Users can view working hours for own staff or if" on public.working_hours;
create policy "Working_Hours: Users can view working hours for own staff or if" on public.working_hours for SELECT to public
  using (((staff_id IN ( SELECT staff.id
   FROM staff
  WHERE (staff.profile_id = auth.uid()))) OR (EXISTS ( SELECT 1
   FROM profiles
  WHERE ((profiles.id = auth.uid()) AND (profiles.role = 'admin'::text))))));

