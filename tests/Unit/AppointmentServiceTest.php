<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Appointment\AppointmentService;
use App\Slot\SlotEngine;

class AppointmentServiceTest extends TestCase
{
    private AppointmentService $appointmentService;
    private \ReflectionClass $reflection;

    protected function setUp(): void
    {
        // Instantiate without constructor: pure helper methods need no Supabase.
        $this->reflection = new \ReflectionClass(AppointmentService::class);
        $this->appointmentService = $this->reflection->newInstanceWithoutConstructor();
    }

    public function testGenerateUuidReturnsValidUuidV4(): void
    {
        $method = $this->reflection->getMethod('generateUuid');
        $method->setAccessible(true);

        $uuid = $method->invoke($this->appointmentService);
        $this->assertIsString($uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
        $this->assertNotSame($uuid, $method->invoke($this->appointmentService));
    }

    public function testGenerateConfirmationCodeReturns8CharString(): void
    {
        $method = $this->reflection->getMethod('generateConfirmationCode');
        $method->setAccessible(true);

        $code = $method->invoke($this->appointmentService);
        $this->assertIsString($code);
        $this->assertSame(8, strlen($code));
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{8}$/', $code);
    }

    public function testSlotEngineFormatTimeSlot(): void
    {
        $this->assertSame('10:00 - 10:30', SlotEngine::formatTimeSlot('10:00', '10:30'));
    }

    public function testSlotEngineGeneratesSlotsForWorkingDay(): void
    {
        $engine = new SlotEngine();
        $staffId = '11111111-1111-1111-1111-111111111111';
        $date = (new \DateTimeImmutable('+7 days'))->format('Y-m-d');
        $dayOfWeek = (int) (new \DateTimeImmutable($date))->format('w');

        $slots = $engine->getAvailableSlots(
            $staffId,
            $date,
            30,
            [
                ['staff_id' => $staffId, 'day_of_week' => $dayOfWeek, 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'is_active' => true],
            ]
        );

        $this->assertNotEmpty($slots);
        $this->assertSame('09:00', $slots[0]['start']);
        $this->assertSame('09:30', $slots[0]['end']);
    }

    public function testSlotEngineKeepsMinutesInWorkingHours(): void
    {
        $engine = new SlotEngine();
        $staffId = '22222222-2222-2222-2222-222222222222';
        $date = (new \DateTimeImmutable('+8 days'))->format('Y-m-d');
        $dayOfWeek = (int) (new \DateTimeImmutable($date))->format('w');

        $slots = $engine->getAvailableSlots(
            $staffId,
            $date,
            30,
            [
                ['staff_id' => $staffId, 'day_of_week' => $dayOfWeek, 'start_time' => '09:30:00', 'end_time' => '10:30:00', 'is_active' => true],
            ]
        );

        // Regression test: (int)'09:30:00' used to drop minutes and start at 09:00
        $this->assertNotEmpty($slots);
        $this->assertSame('09:30', $slots[0]['start']);
    }

    public function testSlotEngineReturnsEmptyForPastDate(): void
    {
        $engine = new SlotEngine();
        $slots = $engine->getAvailableSlots('x', '2000-01-01', 30, []);
        $this->assertSame([], $slots);
    }

    public function testSupabaseRowsHandlesStringJson(): void
    {
        $rows = \App\Services\Supabase::rows('[{"id":"a"},{"id":"b"}]');
        $this->assertSame([['id' => 'a'], ['id' => 'b']], $rows);
    }

    public function testSupabaseRowsHandlesDataEnvelope(): void
    {
        $rows = \App\Services\Supabase::rows(['data' => [['id' => 'a']]]);
        $this->assertSame([['id' => 'a']], $rows);
    }

    public function testSupabaseRowsHandlesSingleRowAssoc(): void
    {
        $rows = \App\Services\Supabase::rows(['id' => 'a', 'confirmation_code' => 'X']);
        $this->assertCount(1, $rows);
        $this->assertSame('a', $rows[0]['id']);
    }

    public function testSupabaseRowsFiltersScalarEntries(): void
    {
        $rows = \App\Services\Supabase::rows([['id' => 'a'], 'bozuk', 42]);
        $this->assertSame([['id' => 'a']], $rows);
    }

    public function testSupabaseRowsThrowsOnErrorEnvelope(): void
    {
        $this->expectException(\RuntimeException::class);
        \App\Services\Supabase::rows(['error' => ['message' => 'boom']]);
    }
}
