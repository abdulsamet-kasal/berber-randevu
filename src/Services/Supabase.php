<?php

declare(strict_types=1);

namespace App\Services;

use Supabase\CreateClient;

class Supabase
{
    private static ?CreateClient $client = null;

    public static function getClient(): CreateClient
    {
        if (self::$client === null) {
            $supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
            $supabaseKey = $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? '';

            if (empty($supabaseUrl) || empty($supabaseKey)) {
                throw new \RuntimeException('Supabase URL and service role key must be set in .env');
            }

            // Parse URL to get reference_id
            $parsed = parse_url($supabaseUrl);
            $host = is_array($parsed) ? ($parsed['host'] ?? '') : '';
            if (!is_string($host) || $host === '') {
                throw new \RuntimeException('Invalid SUPABASE_URL: unable to determine host');
            }
            $suffix = '.supabase.co';
            $reference_id = str_ends_with($host, $suffix)
                ? substr($host, 0, -strlen($suffix))
                : $host;
            if ($reference_id === '') {
                throw new \RuntimeException('Invalid SUPABASE_URL: unable to determine reference_id');
            }

            self::$client = new CreateClient($supabaseKey, $reference_id);
        }

        return self::$client;
    }

    /**
     * Unwrap a Postgrest execute() result into a plain row list.
     *
     * @param mixed $result PostgrestResponse, array, or anything else
     * @return list<array<string, mixed>>
     * @throws \RuntimeException when Supabase reports an error
     */
    public static function rows(mixed $result): array
    {
        // Ham JSON string (bazi client surumleri decode edilmemis body doner)
        if (is_string($result)) {
            $decoded = json_decode($result, true);
            if (is_array($decoded)) {
                return self::rows($decoded);
            }
            return [];
        }
        if (is_array($result)) {
            // Hata zarfi: ['error' => ...]
            if (isset($result['error']) && $result['error'] !== null && $result['error'] !== [] && $result['error'] !== '') {
                $error = $result['error'];
                $message = is_array($error) ? ($error['message'] ?? 'Supabase error') : (string) $error;
                throw new \RuntimeException('Supabase error: ' . $message);
            }
            // Basari zarfi: ['data' => [...]]
            if (array_key_exists('data', $result)) {
                return self::rows($result['data']);
            }
            if ($result === []) {
                return [];
            }
            // Tek satir assoc array ise sar (ornegin ['id' => ...])
            if (array_keys($result) !== range(0, count($result) - 1)) {
                return [$result];
            }
            // Liste: sadece array satirlari tut (scalar bozulmalara karsi defansif)
            return array_values(array_filter(array_values($result), 'is_array'));
        }
        if (is_object($result)) {
            $error = $result->error ?? null;
            if ($error !== null && $error !== [] && $error !== '') {
                $message = is_array($error) ? ($error['message'] ?? 'Supabase error') : (string) $error;
                throw new \RuntimeException('Supabase error: ' . $message);
            }
            $data = $result->data ?? null;
            if ($data === null) {
                return [];
            }
            return self::rows($data);
        }

        throw new \RuntimeException('Unexpected Supabase response type: ' . get_debug_type($result));
    }
}
