<?php

declare(strict_types=1);

namespace App\Appointment;

use App\Slot\SlotEngine;
use App\Services\Supabase;
use Ramsey\Uuid\Uuid;
use Supabase\CreateClient;
use Exception;

class AppointmentService
{
    private SlotEngine $slotEngine;
    private ?CreateClient $supabase = null;

    public function __construct()
    {
        $this->slotEngine = new SlotEngine();
    }

    private function client(): CreateClient
    {
        if ($this->supabase === null) {
            $this->supabase = \App\Services\Supabase::getClient();
        }
        return $this->supabase;
    }

    /**
     * Create an appointment with idempotency support
     *
     * @param array $data Appointment data
     * @return array Result with success status and data or error
     * @throws Exception
     */
    public function createAppointment(array $data): array
    {
        try {
            // Validate required fields
            $required = ['staff_id', 'customer_name', 'customer_phone', 'start_time', 'services', 'idempotency_key'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }

            // KVKK consent is mandatory (server-side enforcement, mirrors the required checkbox)
            $kvkkConsent = filter_var($data['kvkk_consent'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            if (!$kvkkConsent) {
                throw new Exception('KVKK onayi zorunludur (kvkk_consent required)');
            }

            // TR cep telefonu dogrulama (bosluk/tire/parantez tolere edilir)
            $normalizedPhone = preg_replace('/[\s\-\(\)\.]/', '', (string)($data['customer_phone'] ?? ''));
            if (!is_string($normalizedPhone) || !preg_match('/^(?:\+90|90|0)?5\d{9}$/', $normalizedPhone)) {
                throw new Exception('Gecerli bir cep telefonu girin (ornek: 05XX XXX XX XX)');
            }

            // Parse start time first (fail fast on bad input, before any DB call)
            try {
                $startTime = new \DateTimeImmutable($data['start_time'], new \DateTimeZone('Europe/Istanbul'));
            } catch (\Throwable $e) {
                throw new Exception('Invalid start time format');
            }

            // Check idempotency
            $existing = $this->checkIdempotency($data['idempotency_key']);
            if ($existing) {
                return [
                    'success' => true,
                    'message' => 'Appointment already exists with this idempotency key',
                    'data' => $existing
                ];
            }

            // Get staff info
            $staff = $this->getStaffById($data['staff_id']);
            if (!$staff) {
                throw new Exception('Staff not found');
            }

            // Get or create customer
            $customerId = $this->getOrCreateCustomer(
                $data['customer_name'],
                $data['customer_phone'],
                $data['customer_email'] ?? null,
                $kvkkConsent
            );

            // Calculate end time based on services
            $totalDuration = 0;
            $serviceDetails = [];

            foreach ($data['services'] as $serviceId) {
                $service = $this->getServiceById($serviceId);
                if (!$service) {
                    throw new Exception("Service not found: {$serviceId}");
                }

                $totalDuration += (int)$service['duration_minutes'];
                $serviceDetails[] = [
                    'service_id' => $service['id'],
                    'service_name' => $service['name'],
                    'duration_minutes' => $service['duration_minutes'],
                    'price' => $service['price']
                ];
            }

            $endTime = $startTime->modify("+{$totalDuration} minutes");

            // Check slot availability using our slot engine
            $workingHours = $this->getWorkingHoursForStaff($data['staff_id']);
            $timeOff = $this->getTimeOffForStaff($data['staff_id']);
            $existingAppointments = $this->getExistingAppointmentsForStaff($data['staff_id']);

            $availableSlots = $this->slotEngine->getAvailableSlots(
                $data['staff_id'],
                $startTime->format('Y-m-d'),
                $totalDuration,
                $workingHours,
                $timeOff,
                $existingAppointments
            );

            $slotFound = false;
            foreach ($availableSlots as $slot) {
                if ($slot['start'] === $startTime->format('H:i') && $slot['end'] === $endTime->format('H:i')) {
                    $slotFound = true;
                    break;
                }
            }

            if (!$slotFound) {
                throw new Exception('Selected time slot is not available');
            }

            // Insert appointment
            $appointmentId = $this->generateUuid();
            $confirmationCode = $this->generateConfirmationCode();

            $appointmentData = [
                'id' => $appointmentId,
                'staff_id' => $data['staff_id'],
                'customer_id' => $customerId,
                'start_time' => $startTime->format('Y-m-d H:i:s'),
                'end_time' => $endTime->format('Y-m-d H:i:s'),
                'status' => 'booked',
                'confirmation_code' => $confirmationCode,
                'customer_note' => $data['customer_note'] ?? null,
                'internal_note' => $data['internal_note'] ?? null,
                'idempotency_key' => $data['idempotency_key']
            ];

            $result = $this->client()->from('appointments')->insert($appointmentData)->execute();
            Supabase::rows($result); // throws on conflict/error

            // Insert appointment services
            foreach ($serviceDetails as $serviceDetail) {
                $serviceAppointmentId = $this->generateUuid();

                $serviceData = [
                    'id' => $serviceAppointmentId,
                    'appointment_id' => $appointmentId,
                    'service_id' => $serviceDetail['service_id'],
                    'service_name' => $serviceDetail['service_name'],
                    'duration_minutes' => $serviceDetail['duration_minutes'],
                    'price' => $serviceDetail['price']
                ];

                $this->client()->from('appointment_services')->insert($serviceData)->execute();
            }

            return [
                'success' => true,
                'message' => 'Appointment created successfully',
                'data' => [
                    'id' => $appointmentId,
                    'confirmation_code' => $confirmationCode,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'services' => $serviceDetails,
                    'staff_name' => (is_array($staff['profiles'] ?? null) ? ($staff['profiles']['full_name'] ?? null) : null) ?? 'Unknown'
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Check if an appointment already exists with the given idempotency key
     *
     * @param string $idempotencyKey Idempotency key
     * @return array|null Existing appointment data or null
     */
    private function checkIdempotency(string $idempotencyKey): ?array
    {
        $result = $this->client()
            ->from('appointments')
            ->select('id, confirmation_code')
            ->eq('idempotency_key', $idempotencyKey)
            ->limit(1)
            ->execute();

        $rows = Supabase::rows($result);

        return $rows[0] ?? null;
    }

    /**
     * Get staff by ID
     *
     * @param string $staffId Staff UUID
     * @return array|null Staff data or null
     */
    private function getStaffById(string $staffId): ?array
    {
        $result = $this->client()
            ->from('staff')
            ->select('id, profiles(full_name, avatar_url)')
            ->eq('id', $staffId)
            ->limit(1)
            ->execute();

        $rows = Supabase::rows($result);

        return $rows[0] ?? null;
    }

    /**
     * Get or create customer
     *
     * @param string $name Customer name
     * @param string $phone Customer phone
     * @param string|null $email Customer email
     * @param boolean $kvkkConsent KVKK consent status
     * @return string Customer UUID
     */
    private function getOrCreateCustomer(string $name, string $phone, ?string $email, bool $kvkkConsent): string
    {
        // First, try to find existing customer by phone
        $result = $this->client()
            ->from('customers')
            ->select('id')
            ->eq('phone', $phone)
            ->limit(1)
            ->execute();

        $rows = Supabase::rows($result);

        if (!empty($rows)) {
            $existing = $rows[0];

            // Update consent if needed
            if ($kvkkConsent) {
                $this->client()
                    ->from('customers')
                    ->update([
                        'kvkk_consent' => true,
                        'kvkk_consent_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
                    ])
                    ->eq('id', $existing['id'])
                    ->execute();
            }

            return $existing['id'];
        }

        // Create new customer
        $customerId = $this->generateUuid();

        $customerData = [
            'id' => $customerId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'kvkk_consent' => $kvkkConsent,
            'kvkk_consent_at' => $kvkkConsent ? (new \DateTimeImmutable())->format('Y-m-d H:i:s') : null
        ];

        $this->client()->from('customers')->insert($customerData)->execute();

        return $customerId;
    }

    /**
     * Get service by ID
     *
     * @param string $serviceId Service UUID
     * @return array|null Service data or null
     */
    private function getServiceById(string $serviceId): ?array
    {
        $result = $this->client()
            ->from('services')
            ->select('*')
            ->eq('id', $serviceId)
            ->limit(1)
            ->execute();

        $rows = Supabase::rows($result);

        return $rows[0] ?? null;
    }

    /**
     * Get working hours for staff
     *
     * @param string $staffId Staff UUID
     * @return array Working hours data
     */
    private function getWorkingHoursForStaff(string $staffId): array
    {
        $result = $this->client()
            ->from('working_hours')
            ->select('*')
            ->eq('staff_id', $staffId)
            ->eq('is_active', true)
            ->execute();

        return Supabase::rows($result);
    }

    /**
     * Get time off for staff
     *
     * @param string $staffId Staff UUID
     * @return array Time off data
     */
    private function getTimeOffForStaff(string $staffId): array
    {
        $result = $this->client()
            ->from('time_off')
            ->select('*')
            ->eq('staff_id', $staffId)
            ->execute();

        return Supabase::rows($result);
    }

    /**
     * Get existing appointments for staff
     *
     * @param string $staffId Staff UUID
     * @return array Existing appointments data
     */
    private function getExistingAppointmentsForStaff(string $staffId): array
    {
        $result = $this->client()
            ->from('appointments')
            ->select('*')
            ->eq('staff_id', $staffId)
            ->eq('status', 'booked')
            ->execute();

        return Supabase::rows($result);
    }

    /**
     * Generate a UUID
     *
     * @return string UUID
     */
    protected function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Generate a confirmation code
     *
     * @return string Confirmation code
     */
    protected function generateConfirmationCode(): string
    {
        // Generate a random alphanumeric code (8 characters)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excluding confusing characters
        $code = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[random_int(0, $max)];
        }

        return $code;
    }
}
