<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Dosya tabanli basit KV cache (cPanel paylasimli hosting uyumlu: Redis/APCu gerekmez).
 * Atomic yazim (tmp + rename) ile okuma/yazma yarislarini engeller.
 */
class Cache
{
    private string $dir;
    private int $defaultTtl;

    public function __construct(?string $dir = null, int $defaultTtl = 300)
    {
        $this->dir = $dir ?? dirname(__DIR__, 2) . '/storage/cache';
        $this->defaultTtl = $defaultTtl;
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0755, true);
        }
    }

    private function path(string $key): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->dir . '/' . $safe . '.json';
    }

    /**
     * @template T
     * @param callable():T $producer
     * @return T
     */
    public function remember(string $key, callable $producer, ?int $ttl = null)
    {
        $file = $this->path($key);
        $ttl = $ttl ?? $this->defaultTtl;

        if (is_file($file) && (time() - (int) filemtime($file)) < $ttl) {
            $raw = @file_get_contents($file);
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if ($decoded !== null || $raw === 'null') {
                    return $decoded;
                }
            }
        }

        $value = $producer();

        try {
            $tmp = $file . '.' . getmypid() . '.tmp';
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($encoded) && @file_put_contents($tmp, $encoded, LOCK_EX) !== false) {
                @rename($tmp, $file);
            }
        } catch (\Throwable $e) {
            // Cache yazilamazsa sessizce gec (fail-open).
        }

        return $value;
    }

    public function forget(string $key): void
    {
        @unlink($this->path($key));
    }
}
