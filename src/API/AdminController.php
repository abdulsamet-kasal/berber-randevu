<?php

declare(strict_types=1);

namespace App\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController extends BaseController
{
    /**
     * GET /api/admin/appointments?date=YYYY-MM-DD | ?from=YYYY-MM-DD&to=YYYY-MM-DD
     * Tarih araligina gore randevular (musteri + usta adiyla).
     * Opsiyonel filtreler: staff_id, status (booked|completed|cancelled|no_show).
     * Aralık en fazla 62 gun olabilir (performans).
     */
    public function appointments(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $tz = new \DateTimeZone('Europe/Istanbul');
        $today = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');

        $validDate = static function (string $v): bool {
            $o = \DateTimeImmutable::createFromFormat('Y-m-d', $v);
            return $o !== false && $o->format('Y-m-d') === $v;
        };

        $date = trim((string) ($params['date'] ?? ''));
        $from = trim((string) ($params['from'] ?? ''));
        $to = trim((string) ($params['to'] ?? ''));

        if ($date !== '') {
            if (!$validDate($date)) {
                return $this->json($response, ['error' => 'date must be in YYYY-MM-DD format'], 400);
            }
            $from = $date;
            $to = $date;
        } else {
            if ($from === '') {
                $from = $today;
            }
            if ($to === '') {
                $to = $from;
            }
            if (!$validDate($from) || !$validDate($to)) {
                return $this->json($response, ['error' => 'from/to must be in YYYY-MM-DD format'], 400);
            }
            if ($from > $to) {
                return $this->json($response, ['error' => 'from tarihi to tarihinden sonra olamaz'], 400);
            }
            $days = (int) ((new \DateTimeImmutable($to))->getTimestamp() - (new \DateTimeImmutable($from))->getTimestamp()) / 86400;
            if ($days > 62) {
                return $this->json($response, ['error' => 'Tarih araligi en fazla 62 gun olabilir'], 400);
            }
        }

        $staffFilter = trim((string) ($params['staff_id'] ?? ''));
        $statusFilter = trim((string) ($params['status'] ?? ''));
        $allowedStatuses = ['booked', 'completed', 'cancelled', 'no_show'];
        if ($statusFilter !== '' && !in_array($statusFilter, $allowedStatuses, true)) {
            return $this->json($response, ['error' => 'status must be one of: ' . implode(', ', $allowedStatuses)], 400);
        }

        try {
            $query = $this->client()
                ->from('appointments')
                ->select('id, staff_id, customer_id, start_time, end_time, status, confirmation_code, customer_note')
                ->gte('start_time', $from . ' 00:00:00')
                ->lte('start_time', $to . ' 23:59:59');
            if ($staffFilter !== '') {
                $query = $query->eq('staff_id', $staffFilter);
            }
            if ($statusFilter !== '') {
                $query = $query->eq('status', $statusFilter);
            }
            $appointments = \App\Services\Supabase::rows(
                $query->order('start_time', ['ascending' => true])->execute()
            );

            $customerIds = array_values(array_unique(array_filter(array_column($appointments, 'customer_id'))));
            $customers = [];
            if ($customerIds !== []) {
                foreach (\App\Services\Supabase::rows(
                    $this->client()->from('customers')->select('id, name, phone')->in('id', $customerIds)->execute()
                ) as $c) {
                    $customers[$c['id']] = $c;
                }
            }

            $staffIds = array_values(array_unique(array_filter(array_column($appointments, 'staff_id'))));
            $staffNames = [];
            if ($staffIds !== []) {
                $staffRows = \App\Services\Supabase::rows(
                    $this->client()->from('staff')->select('id, profile_id')->in('id', $staffIds)->execute()
                );
                $profileIds = array_values(array_unique(array_filter(array_column($staffRows, 'profile_id'))));
                $profileNames = [];
                if ($profileIds !== []) {
                    foreach (\App\Services\Supabase::rows(
                        $this->client()->from('profiles')->select('id, full_name')->in('id', $profileIds)->execute()
                    ) as $p) {
                        $profileNames[$p['id']] = $p['full_name'] ?? 'Usta';
                    }
                }
                foreach ($staffRows as $s) {
                    $staffNames[$s['id']] = $profileNames[$s['profile_id'] ?? ''] ?? 'Usta';
                }
            }

            $appointmentIds = array_column($appointments, 'id');
            $servicesByAppointment = [];
            if ($appointmentIds !== []) {
                $serviceRows = \App\Services\Supabase::rows(
                    $this->client()
                        ->from('appointment_services')
                        ->select('appointment_id, service_name')
                        ->in('appointment_id', $appointmentIds)
                        ->execute()
                );
                foreach ($serviceRows as $sr) {
                    $servicesByAppointment[$sr['appointment_id']][] = $sr['service_name'] ?? '';
                }
            }

            $out = [];
            foreach ($appointments as $a) {
                $customer = $customers[$a['customer_id'] ?? ''] ?? null;
                $out[] = [
                    'id' => $a['id'],
                    'start_time' => $a['start_time'],
                    'end_time' => $a['end_time'],
                    'status' => $a['status'],
                    'confirmation_code' => $a['confirmation_code'] ?? null,
                    'customer_name' => $customer['name'] ?? 'Bilinmiyor',
                    'customer_phone' => $customer['phone'] ?? null,
                    'staff_name' => $staffNames[$a['staff_id'] ?? ''] ?? 'Bilinmiyor',
                    'services' => $servicesByAppointment[$a['id']] ?? [],
                ];
            }
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Randevular su anda alinamiyor'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => ['date' => $date, 'from' => $from, 'to' => $to, 'count' => count($out), 'appointments' => $out]]);
    }

    /**
     * GET /api/admin/stats — panel istatistikleri.
     */
    public function stats(Request $request, Response $response): Response
    {
        $tz = new \DateTimeZone('Europe/Istanbul');
        $today = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
        $weekStart = (new \DateTimeImmutable('now', $tz))->modify('monday this week')->format('Y-m-d');
        $weekEnd = (new \DateTimeImmutable('now', $tz))->modify('sunday this week')->format('Y-m-d');

        try {
            $todayRows = \App\Services\Supabase::rows(
                $this->client()->from('appointments')->select('id')
                    ->gte('start_time', $today . ' 00:00:00')
                    ->lte('start_time', $today . ' 23:59:59')
                    ->execute()
            );
            $weekRows = \App\Services\Supabase::rows(
                $this->client()->from('appointments')->select('id')
                    ->gte('start_time', $weekStart . ' 00:00:00')
                    ->lte('start_time', $weekEnd . ' 23:59:59')
                    ->execute()
            );
            $staffRows = \App\Services\Supabase::rows(
                $this->client()->from('staff')->select('id')->eq('is_active', true)->execute()
            );
            $serviceRows = \App\Services\Supabase::rows(
                $this->client()->from('services')->select('id')->eq('is_active', true)->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Istatistikler su anda alinamiyor'], 503);
        }

        return $this->json($response, [
            'success' => true,
            'data' => [
                'today' => count($todayRows),
                'week' => count($weekRows),
                'active_staff' => count($staffRows),
                'active_services' => count($serviceRows),
            ],
        ]);
    }

    /**
     * PATCH /api/admin/appointments/{id} — randevu durumunu guncelle.
     * Body: { "status": "booked|completed|cancelled|no_show" }
     */
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = (string) ($args['id'] ?? '');
        if ($id === '') {
            return $this->json($response, ['error' => 'id is required'], 400);
        }

        $allowed = ['booked', 'completed', 'cancelled', 'no_show'];
        $data = $request->getParsedBody();
        $status = is_array($data) ? (string) ($data['status'] ?? '') : '';
        if (!in_array($status, $allowed, true)) {
            return $this->json($response, ['error' => 'status must be one of: ' . implode(', ', $allowed)], 400);
        }

        try {
            \App\Services\Supabase::rows(
                $this->client()
                    ->from('appointments')
                    ->update(['status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s')])
                    ->eq('id', $id)
                    ->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Durum guncellenemedi'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => ['id' => $id, 'status' => $status]]);
    }

    private function invalidateCatalogCache(): void
    {
        try {
            $cache = new \App\Services\Cache();
            $cache->forget('catalog_services');
            $cache->forget('catalog_staff');
        } catch (\Throwable $e) {
        }
    }

    /**
     * GET /api/admin/services — tum hizmetler (pasifler dahil).
     */
    public function listServices(Request $request, Response $response): Response
    {
        try {
            $rows = \App\Services\Supabase::rows(
                $this->client()->from('services')->select('*')->order('sort_order', ['ascending' => true])->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmetler su anda alinamiyor'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => $rows]);
    }

    /**
     * POST /api/admin/services — yeni hizmet.
     * Body: { name, duration_minutes, price, ... }
     */
    public function createService(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $name = trim((string) ($data['name'] ?? ''));
        $duration = (int) ($data['duration_minutes'] ?? 0);
        $price = (float) ($data['price'] ?? -1);
        if ($name === '' || $duration < 5 || $duration > 480 || $price < 0) {
            return $this->json($response, ['error' => 'name, duration_minutes (5-480) ve price (>=0) zorunludur'], 400);
        }

        $sort = 0;
        try {
            $existing = \App\Services\Supabase::rows(
                $this->client()->from('services')->select('sort_order')->order('sort_order', ['ascending' => false])->limit(1)->execute()
            );
            $sort = ((int) ($existing[0]['sort_order'] ?? 0)) + 1;
        } catch (\Throwable $e) {
        }

        $row = [
            'name' => $name,
            'description' => trim((string) ($data['description'] ?? '')),
            'duration_minutes' => $duration,
            'price' => $price,
            'category' => trim((string) ($data['category'] ?? 'Genel')),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $sort,
        ];

        try {
            \App\Services\Supabase::rows($this->client()->from('services')->insert($row)->execute());
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmet eklenemedi'], 503);
        }

        $this->invalidateCatalogCache();

        return $this->json($response, ['success' => true, 'data' => $row], 201);
    }

    /**
     * PATCH /api/admin/services/{id} — hizmet guncelle.
     */
    public function updateService(Request $request, Response $response, array $args): Response
    {
        $id = (string) ($args['id'] ?? '');
        if ($id === '') {
            return $this->json($response, ['error' => 'id is required'], 400);
        }

        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $patch = [];
        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                return $this->json($response, ['error' => 'name bos olamaz'], 400);
            }
            $patch['name'] = $name;
        }
        if (array_key_exists('description', $data)) {
            $patch['description'] = trim((string) $data['description']);
        }
        if (array_key_exists('duration_minutes', $data)) {
            $duration = (int) $data['duration_minutes'];
            if ($duration < 5 || $duration > 480) {
                return $this->json($response, ['error' => 'duration_minutes 5-480 arasinda olmali'], 400);
            }
            $patch['duration_minutes'] = $duration;
        }
        if (array_key_exists('price', $data)) {
            $price = (float) $data['price'];
            if ($price < 0) {
                return $this->json($response, ['error' => 'price negatif olamaz'], 400);
            }
            $patch['price'] = $price;
        }
        if (array_key_exists('category', $data)) {
            $patch['category'] = trim((string) $data['category']);
        }
        if (array_key_exists('is_active', $data)) {
            $patch['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }
        if (array_key_exists('sort_order', $data)) {
            $patch['sort_order'] = (int) $data['sort_order'];
        }
        if ($patch === []) {
            return $this->json($response, ['error' => 'Guncellenecek alan yok'], 400);
        }
        $patch['updated_at'] = gmdate('Y-m-d H:i:s');

        try {
            \App\Services\Supabase::rows(
                $this->client()->from('services')->update($patch)->eq('id', $id)->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmet guncellenemedi'], 503);
        }

        $this->invalidateCatalogCache();

        return $this->json($response, ['success' => true, 'data' => array_merge(['id' => $id], $patch)]);
    }
    /**
     * DELETE /api/admin/services/{id} — hizmet sil (randevusu varsa pasife al).
     */
    public function deleteService(Request $request, Response $response, array $args): Response
    {
        $id = (string) ($args['id'] ?? '');
        if ($id === '') {
            return $this->json($response, ['error' => 'id is required'], 400);
        }

        // Aktif randevuda kullaniliyorsa silme, pasife al (snapshot'lar bozulmasin).
        try {
            $used = \App\Services\Supabase::rows(
                $this->client()->from('appointment_services')->select('id')
                    ->eq('service_id', $id)->limit(1)->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmet kontrol edilemedi'], 503);
        }

        try {
            if (!empty($used)) {
                \App\Services\Supabase::rows(
                    $this->client()->from('services')->update(['is_active' => false, 'updated_at' => gmdate('Y-m-d H:i:s')])->eq('id', $id)->execute()
                );
                $this->invalidateCatalogCache();

                return $this->json($response, ['success' => true, 'data' => ['id' => $id, 'deactivated' => true, 'message' => 'Randevularda kullanildigi icin pasife alindi']]);
            }

            \App\Services\Supabase::rows(
                $this->client()->from('services')->delete()->eq('id', $id)->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmet silinemedi'], 503);
        }

        $this->invalidateCatalogCache();

        return $this->json($response, ['success' => true, 'data' => ['id' => $id, 'deleted' => true]]);
    }

    /**
     * POST /api/admin/staff — yeni usta.
     * Body: { full_name (zorunlu), bio? } — profil (role=staff) + staff satiri acar.
     * Not: Supabase Auth hesabi acmaz; giris gerekiyorsa yonetici Supabase Auth panelinden
     * ayni isimle kullanici acip profil id'sini eslestirmelidir.
     */
    public function createStaff(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $fullName = trim((string) ($data['full_name'] ?? ''));
        if ($fullName === '') {
            return $this->json($response, ['error' => 'full_name zorunludur'], 400);
        }

        try {
            $profileRows = \App\Services\Supabase::rows(
                $this->client()->from('profiles')->insert([
                    'role' => 'staff',
                    'full_name' => $fullName,
                ])->execute()
            );
            $profileId = $profileRows[0]['id'] ?? null;
            if (!is_string($profileId) || $profileId === '') {
                return $this->json($response, ['error' => 'Profil olusturulamadi'], 503);
            }

            $staffRows = \App\Services\Supabase::rows(
                $this->client()->from('staff')->insert([
                    'profile_id' => $profileId,
                    'bio' => trim((string) ($data['bio'] ?? '')),
                    'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ])->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Usta eklenemedi'], 503);
        }

        $this->invalidateCatalogCache();

        return $this->json($response, ['success' => true, 'data' => [
            'id' => $staffRows[0]['id'] ?? null,
            'profile_id' => $profileId,
            'full_name' => $fullName,
        ]], 201);
    }

    /**
     * GET /api/admin/staff — tum ustalar (profil ad + aktif/pasif).
     */
    public function listStaff(Request $request, Response $response): Response
    {
        try {
            $staffRows = \App\Services\Supabase::rows(
                $this->client()->from('staff')->select('id, profile_id, bio, is_active')->execute()
            );

            $profileIds = array_values(array_unique(array_filter(array_column($staffRows, 'profile_id'))));
            $profiles = [];
            if ($profileIds !== []) {
                foreach (\App\Services\Supabase::rows(
                    $this->client()->from('profiles')->select('id, full_name, role')->in('id', $profileIds)->execute()
                ) as $p) {
                    $profiles[$p['id']] = $p;
                }
            }

            $out = [];
            foreach ($staffRows as $s) {
                $p = $profiles[$s['profile_id'] ?? ''] ?? [];
                $out[] = [
                    'id' => $s['id'],
                    'profile_id' => $s['profile_id'] ?? null,
                    'full_name' => $p['full_name'] ?? 'Usta',
                    'bio' => $s['bio'] ?? null,
                    'is_active' => (bool) ($s['is_active'] ?? true),
                ];
            }
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Ustalar su anda alinamiyor'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => $out]);
    }

    /**
     * PATCH /api/admin/staff/{id} — usta guncelle (bio, is_active, full_name).
     */
    public function updateStaff(Request $request, Response $response, array $args): Response
    {
        $id = (string) ($args['id'] ?? '');
        if ($id === '') {
            return $this->json($response, ['error' => 'id is required'], 400);
        }

        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $staffPatch = [];
        if (array_key_exists('bio', $data)) {
            $staffPatch['bio'] = trim((string) $data['bio']);
        }
        if (array_key_exists('is_active', $data)) {
            $staffPatch['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $fullName = array_key_exists('full_name', $data) ? trim((string) $data['full_name']) : null;
        if ($fullName !== null && $fullName === '') {
            return $this->json($response, ['error' => 'full_name bos olamaz'], 400);
        }
        if ($staffPatch === [] && $fullName === null) {
            return $this->json($response, ['error' => 'Guncellenecek alan yok'], 400);
        }

        try {
            $rows = \App\Services\Supabase::rows(
                $this->client()->from('staff')->select('id, profile_id')->eq('id', $id)->limit(1)->execute()
            );
            if (empty($rows)) {
                return $this->json($response, ['error' => 'Usta bulunamadi'], 404);
            }
            $profileId = $rows[0]['profile_id'] ?? null;

            if ($staffPatch !== []) {
                $staffPatch['updated_at'] = gmdate('Y-m-d H:i:s');
                \App\Services\Supabase::rows(
                    $this->client()->from('staff')->update($staffPatch)->eq('id', $id)->execute()
                );
            }

            if ($fullName !== null && $profileId !== null) {
                \App\Services\Supabase::rows(
                    $this->client()->from('profiles')->update(['full_name' => $fullName, 'updated_at' => gmdate('Y-m-d H:i:s')])->eq('id', $profileId)->execute()
                );
            }
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Usta guncellenemedi'], 503);
        }

        $this->invalidateCatalogCache();

        return $this->json($response, ['success' => true, 'data' => array_merge(['id' => $id], $staffPatch, $fullName !== null ? ['full_name' => $fullName] : [])]);
    }
}
