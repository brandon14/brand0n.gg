<?php

/**
 * This file is part of the brandon14/brand0n.gg package.
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

namespace App\Providers;

use App\Services\ApexApi\ApexApi;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiOptions;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use function array_only;

class ApexApiServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        // Bind the Apex API service in the IoC container as a singleton.
        $this->app->singleton('apexapi', static function (Application $app) {
             // Pull out base service config.
            $config = array_only(
                $app->make('config')->get('apexapi'),
                ['cache', 'cache_driver', 'cache_ttl', 'cache_key', 'api_key']
            );

            $isCacheEnabled = (bool) $config['cache'];

            $options    = new ApexApiOptions(
                (string) $config['api_key'],
                $isCacheEnabled,
                (int) $config['cache_ttl'],
                (string) $config['cache_key'],
            );

            return new ApexApi(
                $app->make(Client::class),
                $options,
                $isCacheEnabled ? $app->make('cache')->driver($config['cache_driver'] ?? 'file') : null,
            );
        });

        $this->app->alias('apexapi', ApexApiService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [ApexApiService::class, 'apexapi'];
    }
}
