<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Apex API Data
    |--------------------------------------------------------------------------
    |
    | This option controls whether to cache the Apex API data.
    |
    */

    'cache' => env('APEX_API_CACHE_TIMESTAMP', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the cache driver to use for Apex API service.
    |
    */

    'cache_driver' => env('APEX_API_CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | This option controls the time to cache the API data for.
    |
    */

    'cache_ttl' => env('APEX_API_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | This option controls the cache key value.
    |
    */

    'cache_key' => env('APEX_API_CACHE_KEY', 'apexapi'),

    /*
    |--------------------------------------------------------------------------
    | Apex API Auth Key
    |--------------------------------------------------------------------------
    |
    | The auth key for your Apex API account.
    |
    | @see https://apexlegendsapi.com/documentation.php
    |
    */

    'api_key' => env('APEX_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Hash Algorithm
    |--------------------------------------------------------------------------
    |
    | The has algorithm to use when creating cache keys for the various API endpoints.
    |
    */

    'hash_algo' => 'sha512',
];
