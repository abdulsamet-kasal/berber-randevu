<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Supabase\CreateClient;

class RateLimitMiddleware implements MiddlewareInterface
{
    private ?CreateClient $supabase = null;
    private int $limit; // Requests per window
    private int $window; // Window size in seconds

    public function __construct(int $limit = 60, int $window = 60)
    {
        $this->limit = $limit;
        $this->window = $window;
    }

    private function client(): ?CreateClient
    {
        if ($this->supabase === null) {
            try {
                $this->supabase = \App\Services\Supabase::getClient();
            } catch (\Throwable) {
                return null;
            }
        }
        return $this->supabase;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get endpoint
        $endpoint = $request->getUri()->getPath();
        if (empty($endpoint)) {
            $endpoint = '/';
        }

        // Statik assetler rate-limit'e takilmaz (hem hiz, hem gereksiz Supabase turu yok).
        if (str_starts_with($endpoint, '/assets/')) {
            return $handler->handle($request);
        }

        // Get client IP
        $ipAddress = $this->getClientIp($request);

        // Check rate limit
        if (!$this->isAllowed($ipAddress, $endpoint)) {
            $response = new \Slim\Psr7\Response();
            $payload = json_encode(['error' => 'Too Many Requests']);
            $response->getBody()->write($payload === false ? '{"error":"Too Many Requests"}' : $payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(429);
        }

        return $handler->handle($request);
    }

    /**
     * Check if the request is allowed based on rate limit
     *
     * @param string $ipAddress Client IP address
     * @param string $endpoint Endpoint being accessed
     * @return bool True if allowed
     */
    /**
     * Hizli rate-limit: gunluk pencere + tek upsert (SELECT+UPDATE yerine).
     * Supabase turu: en fazla 1 okuma + 1 yazim; temizlik %1 olasilikla.
     */
    private function isAllowed(string $ipAddress, string $endpoint): bool
    {
        try {
            $client = $this->client();
            if ($client === null) {
                return true; // No DB configured: fail open
            }

            // Gunluk sabit pencere: ayni gun, ayni IP+endpoint icin tek satir.
            $today = (new \DateTimeImmutable('today'))->format('Y-m-d H:i:s');

            $existing = \App\Services\Supabase::rows(
                $client
                    ->from('rate_limits')
                    ->select('id, request_count')
                    ->eq('ip_address', $ipAddress)
                    ->eq('endpoint', $endpoint)
                    ->gte('window_start', $today)
                    ->limit(1)
                    ->execute()
            );

            $count = (int) ($existing[0]['request_count'] ?? 0);
            if ($count >= $this->limit) {
                return false;
            }

            // Tek yazim: varsa artir, yoksa olustur. 003 migration'indaki
            // unique index ayni pencereye cift insert'i engeller.
            try {
                if (!empty($existing[0]['id'])) {
                    $client
                        ->from('rate_limits')
                        ->update(['request_count' => $count + 1])
                        ->eq('id', $existing[0]['id'])
                        ->execute();
                } else {
                    $client
                        ->from('rate_limits')
                        ->insert([
                            'ip_address' => $ipAddress,
                            'endpoint' => $endpoint,
                            'window_start' => $today,
                            'request_count' => 1,
                        ])
                        ->execute();
                }
            } catch (\Throwable $e) {
                // Sayac yazim hatasi istegi engellemez.
            }

            // Temizlik: her 100 istekte yaklasik 1 kez (4 tur yerine ~0 tur).
            if (random_int(1, 100) === 1) {
                $this->cleanupOldEntries();
            }

            return true;
        } catch (\Throwable $e) {
            // On error, allow the request (fail open)
            return true;
        }
    }

    /**
     * Clean up old rate limit entries
     *
     * @return void
     */
    private function cleanupOldEntries(): void
    {
        try {
            $client = $this->client();
            if ($client === null) {
                return;
            }
            $windowStart = (new \DateTimeImmutable())->modify("-{$this->window} seconds")->format('Y-m-d H:i:s');
            $client
                ->from('rate_limits')
                ->delete()
                ->lt('window_start', $windowStart)
                ->execute();
        } catch (\Throwable $e) {
            // Ignore cleanup errors
        }
    }

    /**
     * Get client IP address from request
     *
     * @param Request $request PSR-7 request
     * @return string Client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for forwarded headers first
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        $serverParams = $request->getServerParams();

        foreach ($headers as $header) {
            if (!empty($serverParams[$header] ?? '')) {
                $ips = explode(',', $serverParams[$header]);
                $ip = trim($ips[0]); // Get the first IP

                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Fallback to remote addr
        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
