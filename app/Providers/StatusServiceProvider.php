<?php

/**
 * This file is part of the brandon14/brand0n.gge package.
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

use App\Services\Status\Providers\LaravelWebsiteProvider;
use Brand0nGG\Contracts\Services\Status\StatusOptions;
use Brand0nGG\Services\Status\Providers\PredisProvider;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Brand0nGG\Contracts\Services\Status\StatusService;
use Brand0nGG\Services\Status\StatusService as Status;
use Brand0nGG\Services\Status\Providers\PhpRedisProvider;
use Psr\Http\Message\RequestFactoryInterface;

use function array_only;

class StatusServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider should be deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->registerProviders();
        $this->registerService();
    }

    /**
     * Register configured status service providers. Each provider will
     * be bound to the container as a singleton, aliased to the array key name
     * provided in the config file, and will have a contextual binding set up
     * for when it needs a `$config` constructor param to be passed the config
     * array from the status config file found under the key with the alias
     * name of the provider (i.e. website).
     *
     * @return void
     */
    protected function registerProviders(): void
    {
        $config = $this->app->make('config');
        // Get the list of `'alias' => 'provider'` from the config.
        $providers = $config->get('status.providers');
        $cacheDriver = $config->get('cache.default');
        $redisClient = $config->get('database.redis.client');
        $tags = [];

        // Filter out redis providers if not using redis as the cache service.
        if ($cacheDriver !== 'redis') {
            $providers = array_filter(
                $providers,
                static function ($providerClass) use ($redisClient) {
                    if ($redisClient === 'predis') {
                        return ! $providerClass instanceof PredisProvider;
                    }

                    if ($redisClient === 'phpredis') {
                        return ! $providerClass instanceof PhpRedisProvider;
                    }

                    return true;
                });
        }

        foreach ($providers as $providerName => $providerClass) {
            // Bind driver as a singleton in the container.
            $this->app->singleton($providerClass);

            // When in non-production environments, we need to disable SSL verification
            // for the Guzzle instance we pass into the WebsiteProvider.
            if ($providerClass === LaravelWebsiteProvider::class) {
                $this->app->when(LaravelWebsiteProvider::class)
                    ->needs(Client::class)
                    ->give(static function (Application $app) use ($config) {
                        $guzzleConfig = ['timeout' => $config->get('status.website.timeout', 1)];

                        if ($app->environment() !== 'production') {
                            $guzzleConfig['verify'] = false;
                        }

                        return new Client($guzzleConfig);
                    });

                // Contextual bind for request factory to use the Guzzle bridge.
                $this->app->when(LaravelWebsiteProvider::class)
                    ->needs(RequestFactoryInterface::class)
                    ->give(RequestFactory::class);
            }

            // Set up aliases.
            $this->app->alias($providerClass, "status.${providerName}");

            // Add provider to an array of providers to be tagged as a group.
            $tags[] = $providerClass;
        }

        // Tag all providers under a group to easily resolve when building service.
        $this->app->tag($tags, 'status.providers');
    }

    /**
     * Registers the main status service. Should be registered after
     * the providers have been bound to the IoC.
     *
     * @return void
     */
    protected function registerService(): void
    {
        // Bind the status interface in the IoC container as a
        // singleton.
        $this->app->singleton('status', static function (Application $app) {
            // Pull out base service config.
            $config = array_only(
                $app->make('config')->get('status'),
                ['cache', 'cache_driver', 'cache_ttl', 'cache_key', 'providers']
            );

            $providers = [];

            // Resolve each configured provider and build an 'alias' => 'provider' array to
            // pass into the last modified service class.
            foreach ($config['providers'] as $providerName => $providerClass) {
                $providers[$providerName] = $app->make($providerClass);
            }

            $cacheManager = $app->make('cache');
            $isCacheEnabled = (bool) $config['cache'];

            $statusOptions = new StatusOptions(
                $isCacheEnabled,
                (int) ($config['cache_ttl'] ?? 30),
                (string) ($config['cache_key'] ?? 'status')
            );

            return new Status(
                $isCacheEnabled ? $cacheManager->driver($config['cache_driver'] ?? 'file') : null,
                $statusOptions,
                $providers
            );
        });

        $this->app->alias('status', StatusService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [StatusService::class, 'status'];
    }
}
