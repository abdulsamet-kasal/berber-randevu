<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\API\AppointmentController;
use App\Appointment\AppointmentService;

/**
 * Randevu akisi validation testleri (sunucu gerektirmez, saf PHP mantigi).
 */
class AppointmentEndpointTest extends TestCase
{
    public function testPhoneValidationAcceptsTurkishFormats(): void
    {
        $this->assertTrue(AppointmentController::isValidPhone('0532 123 45 67'));
        $this->assertTrue(AppointmentController::isValidPhone('05321234567'));
        $this->assertTrue(AppointmentController::isValidPhone('+90 532 123 45 67'));
        $this->assertTrue(AppointmentController::isValidPhone('+905321234567'));
        $this->assertTrue(AppointmentController::isValidPhone('905321234567'));
        $this->assertTrue(AppointmentController::isValidPhone('(0532) 123-45-67'));
    }

    public function testPhoneValidationRejectsInvalidNumbers(): void
    {
        $this->assertFalse(AppointmentController::isValidPhone(''));
        $this->assertFalse(AppointmentController::isValidPhone('12345'));
        $this->assertFalse(AppointmentController::isValidPhone('0212 555 12 34')); // sabit hat
        $this->assertFalse(AppointmentController::isValidPhone('0532 123 45')); // eksik
        $this->assertFalse(AppointmentController::isValidPhone('0532 123 45 6a')); // harf
        $this->assertFalse(AppointmentController::isValidPhone('0049 170 1234567')); // yabanci
    }

    /**
     * createAppointment() validation hatalarini yakalayip success:false doner (DB'ye gitmez).
     */
    private function freshService(): AppointmentService
    {
        return (new \ReflectionClass(AppointmentService::class))->newInstanceWithoutConstructor();
    }

    public function testCreateAppointmentFailsOnMissingField(): void
    {
        $result = $this->freshService()->createAppointment([]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Missing required field: staff_id', $result['message']);
    }

    public function testCreateAppointmentFailsWithoutKvkkConsent(): void
    {
        $result = $this->freshService()->createAppointment([
            'staff_id' => '11111111-1111-1111-1111-111111111111',
            'customer_name' => 'Test Musteri',
            'customer_phone' => '0532 123 45 67',
            'start_time' => '2030-01-01 10:00:00',
            'services' => ['22222222-2222-2222-2222-222222222222'],
            'idempotency_key' => 'test-key-kvkk-1',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('KVKK onayi zorunludur', $result['message']);
    }

    public function testCreateAppointmentFailsOnInvalidPhone(): void
    {
        $result = $this->freshService()->createAppointment([
            'staff_id' => '11111111-1111-1111-1111-111111111111',
            'customer_name' => 'Test Musteri',
            'customer_phone' => '0212 555 12 34',
            'start_time' => '2030-01-01 10:00:00',
            'services' => ['22222222-2222-2222-2222-222222222222'],
            'idempotency_key' => 'test-key-phone-1',
            'kvkk_consent' => true,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Gecerli bir cep telefonu girin', $result['message']);
    }

    public function testAdminAppointmentsDateRangeValidation(): void
    {
        $ctrl = new \App\API\AdminController();
        $factory = new \Slim\Psr7\Factory\ServerRequestFactory();
        $respFactory = new \Slim\Psr7\Factory\ResponseFactory();

        // Gecersiz tarih -> 400 (Supabase'e gitmeden)
        $req = $factory->createServerRequest('GET', '/api/admin/appointments?date=gecersiz');
        $res = $ctrl->appointments($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());

        // from > to -> 400
        $req = $factory->createServerRequest('GET', '/api/admin/appointments?from=2026-10-20&to=2026-10-01');
        $res = $ctrl->appointments($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());

        // 62 gunden uzun aralik -> 400
        $req = $factory->createServerRequest('GET', '/api/admin/appointments?from=2026-01-01&to=2026-12-31');
        $res = $ctrl->appointments($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());

        // Gecersiz status -> 400
        $req = $factory->createServerRequest('GET', '/api/admin/appointments?status=yanlis');
        $res = $ctrl->appointments($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());
    }

    public function testCreateStaffValidation(): void
    {
        $ctrl = new \App\API\AdminController();
        $factory = new \Slim\Psr7\Factory\ServerRequestFactory();
        $respFactory = new \Slim\Psr7\Factory\ResponseFactory();

        // Bos isim -> 400 (Supabase'e gitmeden)
        $req = $factory->createServerRequest('POST', '/api/admin/staff')->withParsedBody(['full_name' => '  ']);
        $res = $ctrl->createStaff($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());

        // Body yok -> 400
        $req = $factory->createServerRequest('POST', '/api/admin/staff');
        $res = $ctrl->createStaff($req, $respFactory->createResponse());
        $this->assertSame(400, $res->getStatusCode());
    }

    public function testCreateAppointmentFailsOnInvalidStartTime(): void
    {
        $result = $this->freshService()->createAppointment([
            'staff_id' => '11111111-1111-1111-1111-111111111111',
            'customer_name' => 'Test Musteri',
            'customer_phone' => '0532 123 45 67',
            'start_time' => 'gecersiz-tarih',
            'services' => ['22222222-2222-2222-2222-222222222222'],
            'idempotency_key' => 'test-key-date-1',
            'kvkk_consent' => true,
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid start time format', $result['message']);
    }
}
