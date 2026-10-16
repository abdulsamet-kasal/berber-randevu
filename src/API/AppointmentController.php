<?php

declare(strict_types=1);

namespace App\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Appointment\AppointmentService;

class AppointmentController extends BaseController
{
    private ?AppointmentService $appointmentService = null;

    /**
     * TR cep telefonu dogrulama (bosluk/tire/parantez tolere edilir).
     * Kabul: 05XXXXXXXXX | +905XXXXXXXXX | 905XXXXXXXXX
     */
    public static function isValidPhone(string $phone): bool
    {
        $normalized = preg_replace('/[\s\-\(\)\.]/', '', trim($phone));
        if (!is_string($normalized)) {
            return false;
        }
        return (bool) preg_match('/^(?:\+90|90|0)?5\d{9}$/', $normalized);
    }

    public function __construct()
    {
        parent::__construct();
    }

    private function service(): AppointmentService
    {
        if ($this->appointmentService === null) {
            $this->appointmentService = new AppointmentService();
        }
        return $this->appointmentService;
    }

    public function create(Request $request, Response $response): Response
    {
        // Get JSON body
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        try {
            $svc = $this->service();
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Randevu servisi su anda kullanilamiyor'], 503);
        }
        // Create appointment using our service
        $result = $svc->createAppointment($data);

        if ($result['success']) {
            return $this->json($response, $result);
        } else {
            return $this->json($response, ['error' => $result['message']], 400);
        }
    }

    /**
     * Look up an appointment by confirmation code + customer phone.
     *
     * @return array{appointment: array<string, mixed>|null, customer: array<string, mixed>|null, error: array<string, string>|null, status: int}
     */
    private function findAppointment(string $phone, string $code): array
    {
        try {
            $customers = \App\Services\Supabase::rows(
                $this->client()
                    ->from('customers')
                    ->select('id, name, phone, email')
                    ->eq('phone', $phone)
                    ->limit(1)
                    ->execute()
            );
        } catch (\Throwable $e) {
            return ['appointment' => null, 'customer' => null, 'error' => ['error' => 'Randevu servisi su anda kullanilamiyor'], 'status' => 503];
        }

        if (empty($customers)) {
            return ['appointment' => null, 'customer' => null, 'error' => ['error' => 'Randevu bulunamadi'], 'status' => 404];
        }
        $customer = $customers[0];

        try {
            $appointments = \App\Services\Supabase::rows(
                $this->client()
                    ->from('appointments')
                    ->select('*')
                    ->eq('customer_id', $customer['id'])
                    ->eq('confirmation_code', $code)
                    ->limit(1)
                    ->execute()
            );
        } catch (\Throwable $e) {
            return ['appointment' => null, 'customer' => null, 'error' => ['error' => 'Randevu servisi su anda kullanilamiyor'], 'status' => 503];
        }

        if (empty($appointments)) {
            return ['appointment' => null, 'customer' => $customer, 'error' => ['error' => 'Randevu bulunamadi'], 'status' => 404];
        }

        return ['appointment' => $appointments[0], 'customer' => $customer, 'error' => null, 'status' => 200];
    }

    /**
     * Best-effort fetch of appointment services; returns [] on failure.
     *
     * @return list<array<string, mixed>>
     */
    private function getAppointmentServices(string $appointmentId): array
    {
        try {
            $rows = \App\Services\Supabase::rows(
                $this->client()
                    ->from('appointment_services')
                    ->select('service_id, service_name, duration_minutes, price')
                    ->eq('appointment_id', $appointmentId)
                    ->execute()
            );
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $appointment
     * @param array<string, mixed> $customer
     * @param list<array<string, mixed>> $services
     * @return array<string, mixed>
     */
    private function serializeAppointment(array $appointment, array $customer, array $services): array
    {
        return [
            'id' => $appointment['id'] ?? null,
            'confirmation_code' => $appointment['confirmation_code'] ?? null,
            'status' => $appointment['status'] ?? null,
            'customer_name' => $customer['name'] ?? null,
            'customer_phone' => $customer['phone'] ?? null,
            'staff_id' => $appointment['staff_id'] ?? null,
            'start_time' => $appointment['start_time'] ?? null,
            'end_time' => $appointment['end_time'] ?? null,
            'customer_note' => $appointment['customer_note'] ?? null,
            'services' => $services,
        ];
    }

    public function inquiry(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $phone = trim((string)($queryParams['phone'] ?? ''));
        $code = trim((string)($queryParams['code'] ?? ($queryParams['confirmation_code'] ?? '')));

        if ($phone === '' || $code === '') {
            return $this->json($response, ['error' => 'Phone and code are required'], 400);
        }

        if (!self::isValidPhone($phone)) {
            return $this->json($response, ['error' => 'Gecerli bir telefon numarasi girin (ornek: 05XX XXX XX XX)'], 400);
        }

        $found = $this->findAppointment($phone, $code);
        if ($found['error'] !== null || $found['appointment'] === null || $found['customer'] === null) {
            return $this->json($response, $found['error'] ?? ['error' => 'Randevu bulunamadi'], $found['status']);
        }

        $services = $this->getAppointmentServices((string)$found['appointment']['id']);

        return $this->json($response, [
            'success' => true,
            'data' => [
                'appointment' => $this->serializeAppointment($found['appointment'], $found['customer'], $services),
            ],
        ]);
    }

    public function cancel(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $phone = trim((string)($data['phone'] ?? ''));
        $code = trim((string)($data['confirmation_code'] ?? ($data['code'] ?? '')));

        if ($phone === '' || $code === '') {
            return $this->json($response, ['error' => 'Phone and confirmation code are required'], 400);
        }

        $found = $this->findAppointment($phone, $code);
        if ($found['error'] !== null || $found['appointment'] === null || $found['customer'] === null) {
            return $this->json($response, $found['error'] ?? ['error' => 'Randevu bulunamadi'], $found['status']);
        }

        $appointment = $found['appointment'];
        $status = (string)($appointment['status'] ?? '');

        if ($status === 'cancelled') {
            return $this->json($response, ['error' => 'Randevu zaten iptal edilmis'], 409);
        }

        if ($status !== 'booked') {
            return $this->json($response, ['error' => 'Bu randevu iptal edilemez (durum: ' . $status . ')'], 409);
        }

        try {
            $this->client()
                ->from('appointments')
                ->update(['status' => 'cancelled'])
                ->eq('id', $appointment['id'])
                ->execute();
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Randevu iptal edilirken bir hata olustu'], 503);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Randevu iptal edildi',
            'data' => ['confirmation_code' => $code, 'status' => 'cancelled'],
        ]);
    }

    public function reschedule(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid JSON'], 400);
        }

        $phone = trim((string)($data['phone'] ?? ''));
        $code = trim((string)($data['confirmation_code'] ?? ($data['code'] ?? '')));
        $newStartRaw = trim((string)($data['new_start_time'] ?? ($data['start_time'] ?? '')));

        if ($phone === '' || $code === '' || $newStartRaw === '') {
            return $this->json($response, ['error' => 'Phone, confirmation code and new_start_time are required'], 400);
        }

        $found = $this->findAppointment($phone, $code);
        if ($found['error'] !== null || $found['appointment'] === null || $found['customer'] === null) {
            return $this->json($response, $found['error'] ?? ['error' => 'Randevu bulunamadi'], $found['status']);
        }

        $appointment = $found['appointment'];
        $status = (string)($appointment['status'] ?? '');

        if ($status !== 'booked') {
            return $this->json($response, ['error' => 'Bu randevu ertelenemez (durum: ' . $status . ')'], 409);
        }

        try {
            $newStart = new \DateTimeImmutable($newStartRaw, new \DateTimeZone('Europe/Istanbul'));
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Invalid new_start_time format'], 400);
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Istanbul'));
        if ($newStart <= $now) {
            return $this->json($response, ['error' => 'Yeni randevu zamani gelecekte olmalidir'], 400);
        }

        // Determine duration: sum of booked services, fallback to current start/end diff.
        $services = $this->getAppointmentServices((string)$appointment['id']);
        $durationMinutes = 0;
        foreach ($services as $service) {
            $durationMinutes += (int)($service['duration_minutes'] ?? 0);
        }
        if ($durationMinutes <= 0) {
            try {
                $oldStart = new \DateTimeImmutable((string)$appointment['start_time']);
                $oldEnd = new \DateTimeImmutable((string)$appointment['end_time']);
                $durationMinutes = (int)(($oldEnd->getTimestamp() - $oldStart->getTimestamp()) / 60);
            } catch (\Throwable $e) {
                $durationMinutes = 0;
            }
        }
        if ($durationMinutes <= 0) {
            return $this->json($response, ['error' => 'Randevu suresi belirlenemedi'], 400);
        }

        $newEnd = $newStart->modify('+' . $durationMinutes . ' minutes');

        // Conflict check against other booked appointments of the same staff.
        try {
            $others = \App\Services\Supabase::rows(
                $this->client()
                    ->from('appointments')
                    ->select('id, start_time, end_time')
                    ->eq('staff_id', $appointment['staff_id'])
                    ->eq('status', 'booked')
                    ->execute()
            );
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Randevu servisi su anda kullanilamiyor'], 503);
        }

        $tz = new \DateTimeZone('Europe/Istanbul');
        foreach ($others as $other) {
            if ((string)($other['id'] ?? '') === (string)$appointment['id']) {
                continue;
            }
            try {
                $otherStart = new \DateTimeImmutable((string)$other['start_time'], $tz);
                $otherEnd = new \DateTimeImmutable((string)$other['end_time'], $tz);
            } catch (\Throwable $e) {
                continue;
            }
            if ($newStart < $otherEnd && $newEnd > $otherStart) {
                return $this->json($response, ['error' => 'Secilen zaman dilimi musait degil'], 409);
            }
        }

        try {
            $this->client()
                ->from('appointments')
                ->update([
                    'start_time' => $newStart->format('Y-m-d H:i:s'),
                    'end_time' => $newEnd->format('Y-m-d H:i:s'),
                ])
                ->eq('id', $appointment['id'])
                ->execute();
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Randevu ertelenirken bir hata olustu'], 503);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Randevu ertelendi',
            'data' => [
                'confirmation_code' => $code,
                'start_time' => $newStart->format('Y-m-d H:i:s'),
                'end_time' => $newEnd->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
