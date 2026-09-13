<?php

$backoff = array_values(array_filter(array_map(
    'intval',
    explode(',', (string) env('QUEUE_JOB_BACKOFF', '10,30,60,300')),
), fn (int $seconds) => $seconds > 0));

return [
    'queue' => env('BACKGROUND_QUEUE', 'background'),
    'tries' => max(1, min(10, (int) env('QUEUE_JOB_TRIES', 5))),
    'activity_timeout' => max(10, min(120, (int) env('ACTIVITY_JOB_TIMEOUT', 30))),
    'notification_timeout' => max(10, min(120, (int) env('NOTIFICATION_JOB_TIMEOUT', 45))),
    'push_timeout' => max(10, min(120, (int) env('PUSH_JOB_TIMEOUT', 60))),
    'backoff' => $backoff === [] ? [10, 30, 60, 300] : $backoff,
    'unique_for' => max(60, min(3600, (int) env('QUEUE_JOB_UNIQUE_FOR', 600))),
    'reconcile_batch' => max(10, min(500, (int) env('BACKGROUND_RECONCILE_BATCH', 100))),
    'heartbeat_ttl' => max(120, min(900, (int) env('QUEUE_HEARTBEAT_TTL', 300))),
    'failed_retention_hours' => max(0, (int) env('QUEUE_FAILED_RETENTION_HOURS', 168)),
];
