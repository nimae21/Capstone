<?php

return [
    'cache_seconds' => max(300, min(900, (int) env('CATALOG_CACHE_SECONDS', 600))),
];
