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

use App\Services\LastModified\Providers\LaravelFilesystemLastModifiedProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Last Modified Timestamp
    |--------------------------------------------------------------------------
    |
    | This option controls whether to cache the last modified timestamp.
    |
    */

    'cache' => env('LASTMODIFIED_CACHE_TIMESTAMP', false),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | This option controls the time to cache the timestamp for.
    |
    */

    'cache_ttl' => env('LASTMODIFIED_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | This option controls the cache key value.
    |
    */

    'cache_key' => env('LASTMODIFIED_CACHE_KEY', 'last_modified'),

    /*
    |--------------------------------------------------------------------------
    | Timestamp Format
    |--------------------------------------------------------------------------
    |
    | Default format for the last modified timestamp output.
    |
    */

    'timestamp_format' => env('LASTMODIFIED_TIMESTAMP_FORMAT', 'F jS, Y \a\t h:i:s A T'),

    /*
    |--------------------------------------------------------------------------
    | Last Modified Service Time Providers
    |--------------------------------------------------------------------------
    |
    | This option allows configuration of the time providers that the service
    | will use in order to get the last modified time. These must be resolvable from
    | the IoC container. The key should be the provider name (aliased in the container
    | as well as the config key for the provider) and the value is the class name.
    |
    */

    'providers' => [
        'filesystem' => LaravelFilesystemLastModifiedProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filesystem Time Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This option allows configuration of the filesystem time provider driver.
    |
    */

    'filesystem' => [
        /*
        |--------------------------------------------------------------------------
        | Base Path
        |--------------------------------------------------------------------------
        |
        | The base path to check in. All files in here will be checked, as well as
        | all file in the included directories below.
        |
        */

        'base_path' => env('LASTMODIFIED_FILESYSTEM_BASE_PATH', app()->make('path.base')),

        /*
        |--------------------------------------------------------------------------
        | Included Directories
        |--------------------------------------------------------------------------
        |
        | Array of directories (absolute paths) that the LastModified service will
        | iterate through in addition to checking all files in the above base path.
        |
        */

        'included_directories' => [
            app()->make('path'),
            app()->make('path.config'),
            app()->make('path.public'),
            app()->make('path.database'),
            app()->make('path.resources'),
            app()->make('path.bootstrap'),
            app()->make('path.base'),
            app()->make('path.base').DIRECTORY_SEPARATOR.'bcme',
        ],
    ],
];
