<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    private const VERSION_KEY = 'catalog.data.version';

    public function remember(string $name, Closure $callback, ?int $seconds = null): mixed
    {
        return Cache::remember(
            "catalog.{$name}.v".$this->version(),
            now()->addSeconds($seconds ?? config('catalog.cache_seconds')),
            $callback,
        );
    }

    public function invalidate(): void
    {
        Cache::add(self::VERSION_KEY, 1, now()->addYears(10));
        Cache::increment(self::VERSION_KEY);
    }

    public function version(): int
    {
        return (int) Cache::remember(self::VERSION_KEY, now()->addYears(10), fn () => 1);
    }
}
