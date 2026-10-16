-- Rate limit race-condition korumasi:
-- Ayni IP + endpoint + pencere icin tek satir garantiler (upsert ile artirim guvenli olur).
-- Bu migration Supabase SQL Editor'da calistirilir.
create unique index if not exists uniq_rate_limits_ip_endpoint_window
    on public.rate_limits (ip_address, endpoint, window_start);