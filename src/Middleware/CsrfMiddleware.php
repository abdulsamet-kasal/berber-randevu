<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class CsrfMiddleware implements MiddlewareInterface
{
    private string $tokenName;
    private int $tokenTTL; // Time to live in seconds

    public function __construct(string $tokenName = 'csrf_token', int $tokenTTL = 3600)
    {
        $this->tokenName = $tokenName;
        $this->tokenTTL = $tokenTTL;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // API routes use Bearer tokens, not cookies: exempt them from CSRF
        $path = $request->getUri()->getPath();
        if (str_starts_with($path, '/api/')) {
            return $handler->handle($request);
        }
        // For GET, HEAD, OPTIONS, TRACE requests, we don't need to validate CSRF
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $handler->handle($request);
        }

        // Get the token from the request (parsed body may be null)
        $parsedBody = $request->getParsedBody();
        $bodyToken = is_array($parsedBody) ? ($parsedBody[$this->tokenName] ?? null) : null;
        $queryParams = $request->getQueryParams();
        $candidate = $bodyToken ?? ($queryParams[$this->tokenName] ?? null);
        if (!is_string($candidate) || $candidate === '') {
            $headerToken = $request->getHeaderLine($this->tokenName);
            $candidate = $headerToken !== '' ? $headerToken : null;
        }
        $token = is_string($candidate) ? $candidate : null;

        // Get the token from the session (direct $_SESSION access — never a copy)
        $this->ensureSession();
        $storedToken = $_SESSION[$this->tokenName] ?? null;
        if (!is_string($storedToken)) {
            $storedToken = null;
        }

        // Validate the token (fail-closed: 403 JSON on failure)
        if (!$this->validateToken($token, $storedToken)) {
            $response = new SlimResponse(403);
            $response->getBody()->write((string) json_encode(['error' => 'Invalid CSRF token']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

    /**
     * Generate a CSRF token and store it in the session
     *
     * @return string Generated token
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));

        // Store directly in $_SESSION (by reference, not a copy)
        $this->ensureSession();
        $_SESSION[$this->tokenName] = $token;
        $_SESSION[$this->tokenName . '_expires'] = time() + $this->tokenTTL;

        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * @param string|null $token Token to validate
     * @param string|null $storedToken Stored token from session
     * @return bool True if valid
     */
    private function validateToken(?string $token, ?string $storedToken): bool
    {
        if (!$token || !$storedToken) {
            return false;
        }

        // Check if token has expired (direct $_SESSION access)
        $this->ensureSession();
        $expires = $_SESSION[$this->tokenName . '_expires'] ?? 0;

        if (time() > $expires) {
            // Token expired, remove it
            unset($_SESSION[$this->tokenName]);
            unset($_SESSION[$this->tokenName . '_expires']);
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($token, $storedToken);
    }

    /**
     * Ensure the PHP session is started.
     */
    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
