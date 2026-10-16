<?php

declare(strict_types=1);

namespace App\Notification;

use Supabase\CreateClient;
use App\Services\Supabase;

class RealtimeService
{
    private ?CreateClient $supabase = null;

    public function __construct()
    {
    }

    private function client(): CreateClient
    {
        if ($this->supabase === null) {
            $this->supabase = Supabase::getClient();
        }
        return $this->supabase;
    }

    /**
     * Subscribe to appointment changes for a staff member
     *
     * @param string $staffId Staff UUID
     * @param callable $callback Function to call when appointment changes
     * @return void
     */
    public function subscribeToStaffAppointments(string $staffId, callable $callback): void
    {
        // In a real implementation, we would use Supabase Realtime
        // For now, this is a placeholder
        /*
        $this->supabase
            ->from('appointments')
            ->on('UPDATE', $callback)
            ->eq('staff_id', $staffId)
            ->subscribe();
        */
    }

    /**
     * Subscribe to new appointments
     *
     * @param callable $callback Function to call when new appointment is created
     * @return void
     */
    public function subscribeToNewAppointments(callable $callback): void
    {
        // Placeholder for Supabase Realtime subscription
        /*
        $this->supabase
            ->from('appointments')
            ->on('INSERT', $callback)
            ->subscribe();
        */
    }

    /**
     * Send a notification to a user
     *
     * @param string $userId User UUID (from auth.users)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $relatedUuid Related record UUID (appointment, review, etc.)
     * @param string $type Notification type (appointment_created, appointment_cancelled, etc.)
     * @return void
     */
    public function sendNotification(
        string $userId,
        string $title,
        string $message,
        ?string $relatedUuid = null,
        string $type = 'general'
    ): void {
        try {
            $this->client()
                ->from('notifications')
                ->insert([
                    'recipient_uuid' => $userId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'related_uuid' => $relatedUuid,
                ])
                ->execute();
        } catch (\Throwable $e) {
            // Best-effort: notification failures must not break the caller
        }
    }
}
