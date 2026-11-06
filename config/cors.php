<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The browser frontend and backend currently share the same origin, so
    | browser requests do not need cross-origin permissions. Keep the CORS
    | middleware available for API routes, but do not authorize arbitrary
    | browser origins. If a separate frontend is introduced, add its exact
    | HTTPS origin and only the methods and headers it requires.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['POST', 'OPTIONS'],

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
