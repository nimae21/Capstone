<?php

namespace App\Observers;

use App\Services\CatalogCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class CatalogCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        app(CatalogCache::class)->invalidate();
    }

    public function deleted(Model $model): void
    {
        app(CatalogCache::class)->invalidate();
    }

    public function restored(Model $model): void
    {
        app(CatalogCache::class)->invalidate();
    }
}
