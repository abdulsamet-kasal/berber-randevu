<?php

declare(strict_types=1);

namespace App\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController
{
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return $this->json($response, ['error' => 'Invalid request body'], 400);
        }

        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');

        if ($email === '' || $password === '') {
            return $this->json($response, ['error' => 'Email and password are required'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ['error' => 'Invalid email format'], 400);
        }

        // Authenticate against Supabase Auth REST API (POST .../auth/v1/token?grant_type=password).
        // The SDK's signInWithPassword performs exactly this request.
        try {
            $result = $this->client()->auth->signInWithPassword([
                'email' => $email,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            // Network / service unreachable: never fall back to a fake token.
            return $this->json($response, ['error' => 'Authentication service unavailable'], 503);
        }

        if (!is_array($result) || ($result['error'] ?? null) !== null) {
            return $this->json($response, ['error' => 'Invalid email or password'], 401);
        }

        // gotrue-php returns the session flat under data (access_token, user, expires_in).
        $data = is_array($result['data'] ?? null) ? $result['data'] : [];
        $token = $data['access_token'] ?? null;
        $user = is_array($data['user'] ?? null) ? $data['user'] : [];

        if (!is_string($token) || $token === '') {
            return $this->json($response, ['error' => 'Invalid email or password'], 401);
        }

        $userMeta = is_array($user['user_metadata'] ?? null) ? $user['user_metadata'] : [];
        $appMeta = is_array($user['app_metadata'] ?? null) ? $user['app_metadata'] : [];
        $role = $userMeta['role'] ?? $appMeta['role'] ?? $user['role'] ?? null;

        $payload = [
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $data['expires_in'] ?? null,
                'user' => [
                    'id' => $user['id'] ?? null,
                    'email' => $user['email'] ?? $email,
                    'role' => $role,
                ],
            ],
        ];

        // /admin HTML guard icin HttpOnly session cookie (tarayici sekmelerinde calisir).
        $secure = filter_var($_ENV['SESSION_SECURE'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $cookie = 'br_admin_token=' . $token
            . '; Path=/admin; Max-Age=86400; SameSite=Lax'
            . ($secure ? '; Secure' : '')
            . '; HttpOnly';

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json === false ? '{}' : $json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Set-Cookie', $cookie);
    }

    public function logout(Request $request, Response $response): Response
    {
        try {
            $this->client()->auth->signOut();
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Authentication service unavailable'], 503);
        }

        $payload = ['success' => true, 'message' => 'Logout successful'];
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json === false ? '{}' : $json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Set-Cookie', 'br_admin_token=; Path=/admin; Max-Age=0; SameSite=Lax; HttpOnly');
    }
}
