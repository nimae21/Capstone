<?php

return [
    'aggregation_minutes' => max(1, (int) env('ACTIVITY_AGGREGATION_MINUTES', 60)),

    // Zero disables pruning. Set explicitly only after agreeing a retention policy.
    'retention_days' => max(0, (int) env('ACTIVITY_RETENTION_DAYS', 0)),
    'prune_batch' => max(100, (int) env('ACTIVITY_PRUNE_BATCH', 1000)),
];
