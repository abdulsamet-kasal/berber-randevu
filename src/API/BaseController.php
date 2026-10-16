<?php

declare(strict_types=1);

namespace App\API;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Supabase\CreateClient;

abstract class BaseController
{
    protected ?CreateClient $supabase = null;

    public function __construct()
    {
    }

    protected function client(): CreateClient
    {
        if ($this->supabase === null) {
            $this->supabase = \App\Services\Supabase::getClient();
        }
        return $this->supabase;
    }

    protected function json(Response $response, mixed $data, int $status = 200): Response
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($json === false ? '{}' : $json);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    protected function getUserIdFromRequest(Request $request): ?string
    {
        // In a real app, we would validate JWT or session
        // For now, we'll return null
        return null;
    }
}
