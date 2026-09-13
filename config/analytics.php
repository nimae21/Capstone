<?php

return [
    // Versioned keys make invalidation work with Redis, database, file, and
    // array stores without relying on cache tags.
    'dashboard_ttl' => max(30, min(600, (int) env('ANALYTICS_DASHBOARD_TTL', 120))),
    'api_ttl' => max(30, min(600, (int) env('ANALYTICS_API_TTL', 120))),
    'report_ttl' => max(60, min(900, (int) env('ANALYTICS_REPORT_TTL', 300))),
    'ranking_limit' => max(1, min(10, (int) env('ANALYTICS_RANKING_LIMIT', 5))),
    'report_ranking_limit' => max(1, min(25, (int) env('ANALYTICS_REPORT_RANKING_LIMIT', 10))),
];
