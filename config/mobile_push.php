<?php

return [
    'enabled' => env('MOBILE_PUSH_ENABLED', false),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    'device_lifetime_days' => 30,
];
