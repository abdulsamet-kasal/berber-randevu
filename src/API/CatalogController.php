<?php

declare(strict_types=1);

namespace App\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CatalogController extends BaseController
{
    /** GET /api/services — aktif hizmetler (randevu formu için, 5 dk cache) */
    public function services(Request $request, Response $response): Response
    {
        try {
            $cache = new \App\Services\Cache();
            $rows = $cache->remember('catalog_services', function () {
                return \App\Services\Supabase::rows(
                    $this->client()
                        ->from('services')
                        ->select('id, name, description, duration_minutes, price, category')
                        ->eq('is_active', true)
                        ->order('sort_order', ['ascending' => true])
                        ->execute()
                );
            }, 300);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Hizmetler şu anda alınamıyor'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => $rows]);
    }

    /** GET /api/staff — aktif ustalar (profil adlarıyla, 5 dk cache) */
    public function staff(Request $request, Response $response): Response
    {
        try {
            $cache = new \App\Services\Cache();
            $out = $cache->remember('catalog_staff', function () {
                return $this->loadStaff();
            }, 300);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Ustalar şu anda alınamıyor'], 503);
        }

        return $this->json($response, ['success' => true, 'data' => $out]);
    }

    /** @return list<array{id: mixed, full_name: mixed, bio: mixed}> */
    private function loadStaff(): array
    {
        $staffRows = \App\Services\Supabase::rows(
                $this->client()
                    ->from('staff')
                    ->select('id, profile_id, bio')
                    ->eq('is_active', true)
                    ->execute()
            );

            $profileIds = array_values(array_filter(array_column($staffRows, 'profile_id')));
            $profiles = [];
            if ($profileIds !== []) {
                $profileRows = \App\Services\Supabase::rows(
                    $this->client()
                        ->from('profiles')
                        ->select('id, full_name')
                        ->in('id', $profileIds)
                        ->execute()
                );
                foreach ($profileRows as $p) {
                    $profiles[$p['id']] = $p['full_name'] ?? null;
                }
            }

            $out = [];
            foreach ($staffRows as $s) {
                $out[] = [
                    'id' => $s['id'],
                    'full_name' => $profiles[$s['profile_id'] ?? ''] ?? 'Usta',
                    'bio' => $s['bio'] ?? null,
                ];
            }

            return $out;
    }
}
