<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

/**
 * /api/admin/* rotalarını korur.
 * Authorization: Bearer <supabase access_token> başlığını doğrular
 * (Gotrue /auth/v1/user) ve kullanıcının admin rolü olmasını şart koşar.
 */
class AdminAuthMiddleware implements MiddlewareInterface
{
    private ?array $cachedUser = null;

    public function process(Request $request, RequestHandler $handler): Response
    {
        $user = $this->authenticate($request);

        if ($user === null) {
            return $this->unauthorized($request, 'Authentication required');
        }

        if (!$this->isAdmin($user)) {
            return $this->forbidden($request, 'Admin privileges required');
        }

        return $handler->handle($request->withAttribute('admin_user', $user));
    }

    private function authenticate(Request $request): ?array
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header === '' || !preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            return null;
        }
        $token = $m[1];

        if ($this->cachedUser !== null && ($this->cachedUser['_token'] ?? '') === $token) {
            return $this->cachedUser;
        }

        $url = rtrim((string) ($_ENV['SUPABASE_URL'] ?? ''), '/') . '/auth/v1/user';
        if ($url === '/auth/v1/user') {
            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", [
                    'apikey: ' . ($_ENV['SUPABASE_ANON_KEY'] ?? ''),
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                ]),
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return null;
        }

        $user = json_decode($body, true);
        if (!is_array($user) || empty($user['id'])) {
            return null;
        }

        $user['_token'] = $token;
        $this->cachedUser = $user;

        return $user;
    }

    private function isAdmin(array $user): bool
    {
        $userMeta = is_array($user['user_metadata'] ?? null) ? $user['user_metadata'] : [];
        $appMeta = is_array($user['app_metadata'] ?? null) ? $user['app_metadata'] : [];

        $role = $userMeta['role'] ?? $appMeta['role'] ?? $user['role'] ?? null;
        if (is_string($role) && strtolower($role) === 'admin') {
            return true;
        }

        // profiles tablosundaki role alanına bak (best-effort)
        try {
            $rows = \App\Services\Supabase::rows(
                \App\Services\Supabase::getClient()
                    ->from('profiles')
                    ->select('role')
                    ->eq('id', $user['id'])
                    ->limit(1)
                    ->execute()
            );
            if (!empty($rows[0]['role']) && strtolower((string) $rows[0]['role']) === 'admin') {
                return true;
            }
        } catch (\Throwable $e) {
            // yut; aşağıdaki red geçerli
        }

        return false;
    }

    private function unauthorized(Request $request, string $message): Response
    {
        return $this->jsonError($request, $message, 401);
    }

    private function forbidden(Request $request, string $message): Response
    {
        return $this->jsonError($request, $message, 403);
    }

    private function jsonError(Request $request, string $message, int $status): Response
    {
        $response = new SlimResponse();
        $body = json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($body === false ? '{}' : $body);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
