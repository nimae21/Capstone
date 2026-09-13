<?php

namespace App\Observers;

use App\Services\AnalyticsService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class AnalyticsCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        app(AnalyticsService::class)->invalidate();
    }

    public function deleted(Model $model): void
    {
        app(AnalyticsService::class)->invalidate();
    }

    public function restored(Model $model): void
    {
        app(AnalyticsService::class)->invalidate();
    }
}
