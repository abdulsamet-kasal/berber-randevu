<?php

declare(strict_types=1);

namespace App\Slot;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class SlotEngine
{
    private const TIMEZONE = 'Europe/Istanbul';

    /**
     * Parse a "HH:MM:SS" (or "HH:MM") time string into [hour, minute, second].
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private static function parseTimeToParts(string $time): array
    {
        $chunks = explode(':', $time);
        $hour = (int) ($chunks[0] ?? 0);
        $minute = (int) ($chunks[1] ?? 0);
        $second = (int) ($chunks[2] ?? 0);

        return [$hour, $minute, $second];
    }

    /**
     * Get available slots for a staff member on a specific date
     *
     * @param string $staffId Staff UUID
     * @param string $date Date in YYYY-MM-DD format
     * @param int $serviceDuration Service duration in minutes
     * @param array $workingHours Working hours from DB
     * @param array $timeOff Time off periods from DB
     * @param array $existingAppointments Existing appointments from DB
     * @param int $bufferTime Buffer time between appointments (minutes)
     * @param int $minLeadTime Minimum lead time for booking (minutes)
     * @param int $maxAdvanceDays Maximum days in advance for booking
     * @return array Array of available time slots
     * @throws Exception
     */
    public function getAvailableSlots(
        string $staffId,
        string $date,
        int $serviceDuration,
        array $workingHours = [],
        array $timeOff = [],
        array $existingAppointments = [],
        int $bufferTime = 15,
        int $minLeadTime = 30,
        int $maxAdvanceDays = 30
    ): array {
        // Validate date format
        $dateObj = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        if (!$dateObj) {
            throw new Exception('Invalid date format');
        }

        // Check if date is in the past
        $now = new DateTimeImmutable('now', new DateTimeZone(self::TIMEZONE));
        $today = $now->setTime(0, 0, 0);
        if ($dateObj < $today) {
            return []; // No slots for past dates
        }

        // Check if date is too far in the future
        $maxDate = $today->modify("+{$maxAdvanceDays} days");
        if ($dateObj > $maxDate) {
            return []; // No slots for dates too far in future
        }

        // Get day of week (0 = Sunday, 1 = Monday, etc.)
        $dayOfWeek = (int)$dateObj->format('w');

        // Find working hours for this staff member and day of week
        $staffWorkingHours = null;
        foreach ($workingHours as $wh) {
            if ((string)$wh['staff_id'] === $staffId && (int)$wh['day_of_week'] === $dayOfWeek && (bool)$wh['is_active']) {
                $staffWorkingHours = $wh;
                break;
            }
        }

        if (!$staffWorkingHours) {
            return []; // No working hours for this day
        }

        // Apply to the specific date
        [$startH, $startM, $startS] = self::parseTimeToParts((string) $staffWorkingHours['start_time']);
        [$endH, $endM, $endS] = self::parseTimeToParts((string) $staffWorkingHours['end_time']);
        $workStart = $dateObj->setTime($startH, $startM, $startS);
        $workEnd = $dateObj->setTime($endH, $endM, $endS);

        // Handle break time if specified
        $breakStart = null;
        $breakEnd = null;
        if (!empty($staffWorkingHours['break_start']) && !empty($staffWorkingHours['break_end'])) {
            [$breakSH, $breakSM, $breakSS] = self::parseTimeToParts((string) $staffWorkingHours['break_start']);
            [$breakEH, $breakEM, $breakES] = self::parseTimeToParts((string) $staffWorkingHours['break_end']);
            $breakStart = $dateObj->setTime($breakSH, $breakSM, $breakSS);
            $breakEnd = $dateObj->setTime($breakEH, $breakEM, $breakES);
        }

        // Generate all possible slots
        $allSlots = [];
        $currentSlotStart = $workStart;

        while ($currentSlotStart < $workEnd) {
            $currentSlotEnd = $currentSlotStart->modify("+{$serviceDuration} minutes");

            // Check if slot is within working hours
            if ($currentSlotEnd > $workEnd) {
                break;
            }

            // Check if slot conflicts with break time
            $conflictsWithBreak = false;
            if ($breakStart instanceof DateTimeImmutable && $breakEnd instanceof DateTimeImmutable) {
                if (
                    ($currentSlotStart < $breakEnd && $currentSlotEnd > $breakStart) ||
                    ($currentSlotStart >= $breakStart && $currentSlotStart < $breakEnd)
                ) {
                    $conflictsWithBreak = true;
                }
            }

            if (!$conflictsWithBreak) {
                $allSlots[] = [
                    'start' => $currentSlotStart,
                    'end' => $currentSlotEnd,
                ];
            }

            // Move to next slot (add service duration + buffer time)
            $currentSlotStart = $currentSlotEnd->modify("+{$bufferTime} minutes");
        }

        // Filter out slots that conflict with time off
        $filteredSlots = [];
        foreach ($allSlots as $slot) {
            $conflictsWithTimeOff = false;
            foreach ($timeOff as $off) {
                // time_off.is_active column may not exist in DB — default to active (backward-compatible)
                if ((string)$off['staff_id'] !== $staffId || !($off['is_active'] ?? true)) {
                    continue;
                }

                $startRaw = $off['start_date'] ?? null;
                if (!is_string($startRaw) || $startRaw === '') {
                    continue; // Skip entries without a valid start date
                }
                $offStart = DateTimeImmutable::createFromFormat('Y-m-d', $startRaw, new DateTimeZone(self::TIMEZONE));
                $endRaw = $off['end_date'] ?? null;
                $offEnd = is_string($endRaw) && $endRaw !== ''
                    ? DateTimeImmutable::createFromFormat('Y-m-d', $endRaw, new DateTimeZone(self::TIMEZONE))
                    : $offStart; // Single day off

                if ($offStart === false) {
                    continue; // Skip malformed time-off entries
                }
                if ($offEnd === false) {
                    $offEnd = $offStart;
                }

                // Set time to cover entire day
                $offStart = $offStart->setTime(0, 0, 0);
                $offEnd = $offEnd->setTime(23, 59, 59);

                if (
                    $slot['start'] >= $offStart && $slot['start'] <= $offEnd
                ) {
                    $conflictsWithTimeOff = true;
                    break;
                }
            }

            if (!$conflictsWithTimeOff) {
                $filteredSlots[] = $slot;
            }
        }

        // Filter out slots that conflict with existing appointments
        $availableSlots = [];
        foreach ($filteredSlots as $slot) {
            $conflictsWithAppointment = false;
            foreach ($existingAppointments as $appointment) {
                if ((string)$appointment['staff_id'] !== $staffId || $appointment['status'] !== 'booked') {
                    continue;
                }

                $appointmentStart = new DateTimeImmutable($appointment['start_time'], new DateTimeZone(self::TIMEZONE));
                $appointmentEnd = new DateTimeImmutable($appointment['end_time'], new DateTimeZone(self::TIMEZONE));

                // Check for overlap
                if (
                    $slot['start'] < $appointmentEnd &&
                    $slot['end'] > $appointmentStart
                ) {
                    $conflictsWithAppointment = true;
                    break;
                }
            }

            if (!$conflictsWithAppointment) {
                // Check minimum lead time
                $now = new DateTimeImmutable('now', new DateTimeZone(self::TIMEZONE));
                $minStartTime = $now->modify("+{$minLeadTime} minutes");

                if ($slot['start'] >= $minStartTime) {
                    $availableSlots[] = [
                        'start' => $slot['start']->format('H:i'),
                        'end' => $slot['end']->format('H:i'),
                        'start_datetime' => $slot['start'],
                        'end_datetime' => $slot['end'],
                    ];
                }
            }
        }

        return $availableSlots;
    }

    /**
     * Format time slot for display
     *
     * @param string $start Start time in H:i format
     * @param string $end End time in H:i format
     * @return string Formatted time slot (e.g., "10:00 - 10:30")
     */
    public static function formatTimeSlot(string $start, string $end): string
    {
        return "{$start} - {$end}";
    }
}
