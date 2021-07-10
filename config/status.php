<?php

/**
 * This file is part of the brandon14/brandonclothier.me package.
 *
 * Copyright 2017-2020 Brandon Clothier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

declare(strict_types=1);

use App\Services\Status\Providers\Pdo\PdoPingQuery;
use App\Services\Status\Providers\LaravelPdoProvider;
use App\Services\Status\Providers\LaravelPredisProvider;
use App\Services\Status\Providers\LaravelOpcacheProvider;
use App\Services\Status\Providers\LaravelWebsiteProvider;
use App\Services\Status\Providers\LaravelPhpRedisProvider;
use App\Services\Status\Providers\LaravelApplicationProvider;
use App\Services\Status\Providers\Pdo\PostgresPdoDetailsQuery;

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Last Modified Timestamp
    |--------------------------------------------------------------------------
    |
    | This option controls whether to cache the status providers.
    |
    */

    'cache' => env('STATUS_CACHE_STATUSES', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the cache driver to use for status service.
    |
    */

    'cache_driver' => env('STATUS_CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | This option controls the time to cache the statuses for.
    |
    */

    'cache_ttl' => env('STATUS_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | This option controls the cache key value.
    |
    */

    'cache_key' => env('STATUS_CACHE_KEY', 'status'),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Array of provider name => provider class names.
    |
    */

    'providers' => [
        'application' => LaravelApplicationProvider::class,
        'database'    => LaravelPdoProvider::class,
        'opcache'     => LaravelOpcacheProvider::class,
        'redis'       => config('database.redis.client') === 'phpredis' ? LaravelPhpRedisProvider::class : LaravelPredisProvider::class,
        'website'     => LaravelWebsiteProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Data
    |--------------------------------------------------------------------------
    | Data used to construct the various status providers.
    |
    */

    'opcache' => [
        'detail_key' => env('STATUS_OPCACHE_DETAIL_KEY', 'details'),
    ],

    'application' => [
        'laravel_version' => app()->version(),
        'app_version'     => config('app.version'),
    ],

    'pdo' => [
        'connection'    => env('STATUS_PDO_CONNECTION', 'pgsql'),
        'ping_query'    => new PdoPingQuery(),
        'details_query' => new PostgresPdoDetailsQuery(
            (string) env('STATUS_PDO_DATABASE_NAME', env('DB_DATABASE', 'homestead'))
        ),
        'detail_key'    => env('STATUS_PDO_DETAIL_KEY', 'details'),
    ],

    'website' => [
        'route_to_ping' => env('STATUS_WEBSITE_ROUTE_TO_PING', 'home'),
        'timeout'       => env('STATUS_WEBSITE_TIMEOUT', 5),
        'desired_time'  => env('STATUS_WEBSITE_DESIRED_RESPONSE_TIME', 200),
        'add_headers'   => (bool) env('STATUS_WEBSITE_ADD_HEADERS', true),
        'add_time'      => (bool) env('STATUS_WEBSITE_ADD_TIME', true),
        'detail_key'    => env('STATUS_WEBSITE_DETAIL_KEY', 'details'),
    ],

    'redis' => [
        'connection_name' => env('STATUS_REDIS_CONNECTION_NAME', 'default'),
        'detail_key'      => env('STATUS_REDIS_DETAIL_KEY', 'details'),
        'info_commands'   => explode(',', env('STATUS_REDIS_INFO_COMMANDS', 'INFO,STATS,SERVER,CLIENTS,MEMORY,CPU,COMMANDSTATS')),
        'excluded_keys'   => explode(',', env('STATUS_REDIS_INFO_COMMANDS', 'tcp_port,executable,config_file')),
    ],
];
