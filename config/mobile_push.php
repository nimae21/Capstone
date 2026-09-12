<?php

return [
    'enabled' => env('MOBILE_PUSH_ENABLED', false),

    /*
    |----------------------------------------------------------------------
    | Firebase project
    |----------------------------------------------------------------------
    | Used to build the FCM HTTP v1 endpoint. When it is not set, the project
    | id inside the service-account key is used instead, so a container host
    | only needs the credential variable.
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |----------------------------------------------------------------------
    | Service-account credentials
    |----------------------------------------------------------------------
    | Two sources, most portable first:
    |
    |  - credentials_json: the entire service-account JSON in one variable
    |    (FIREBASE_SERVICE_ACCOUNT_JSON). This is how Railway and other
    |    container hosts are configured: nothing is written to disk.
    |  - credentials: an absolute path to the JSON file
    |    (GOOGLE_APPLICATION_CREDENTIALS), kept for local development.
    |
    | The values are read but never echoed. FirebasePushSender reports only why
    | a source was rejected, never what it contained.
    */
    'credentials_json' => env('FIREBASE_SERVICE_ACCOUNT_JSON'),
    'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),

    'device_lifetime_days' => 30,
];
