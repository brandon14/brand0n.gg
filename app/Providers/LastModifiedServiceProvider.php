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

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Brand0nGG\Services\LastModified\LastModified;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedOptions;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedService;
use Brand0nGG\Services\LastModified\Providers\FilesystemLastModifiedTimeProvider;
use function array_only;

class LastModifiedServiceProvider extends ServiceProvider
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
        $this->registerDrivers();
        $this->registerService();
    }

    /**
     * Register configured last modified time providers. Each provider will
     * be bound to the container as a singleton, aliased to the array key name
     * provided in the config file, and will have a contextual binding set up
     * for when it needs a `$config` constructor param to be passed the config
     * array from the lastmodified config file found under the key with the alias
     * name of the driver (i.e. filesystem).
     *
     * @return void
     */
    protected function registerDrivers(): void
    {
        // Get the list of `'alias' => 'provider'` from the config.
        $providers = $this->app->make('config')->get('lastmodified.providers');

        foreach ($providers as $alias => $provider) {
            // Bind driver as a singleton in the container.
            $this->app->singleton($provider);

            // Set up aliases.
            $this->app->alias($provider, "lastmodified.${alias}");
        }
    }

    /**
     * Registers the main last modified service. Should be registered after
     * the providers have been bound to the IoC.
     *
     * @return void
     */
    protected function registerService(): void
    {
        // Bind the last modified interface in the IoC container as a
        // singleton.
        $this->app->singleton('lastmodified', static function (Application $app) {
            // Pull out base service config.
            $config = array_only(
                $app->make('config')->get('lastmodified'),
                ['cache', 'cache_driver', 'cache_ttl', 'cache_key', 'default_timestamp_format', 'providers']
            );

            $providers = [];

            // Resolve each configured provider and build an 'alias' => 'provider' array to
            // pass into the last modified service class.
            foreach ($config['providers'] as $providerName => $providerClass) {
                $providers[$providerName] = $app->make($providerClass);
            }

            $isCacheEnabled = (bool) $config['cache'];

            $options = new LastModifiedOptions(
                $isCacheEnabled,
                (int) $config['cache_ttl'],
                (string) $config['cache_key'],
                (string) $config['default_timestamp_format']
            );

            return new LastModified(
                $isCacheEnabled ? $app->make('cache')->driver($config['cache_driver'] ?? 'file') : null,
                $options,
                $providers
            );
        });

        $this->app->alias('lastmodified', LastModifiedService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function provides(): array
    {
        return [LastModifiedService::class, 'lastmodified'];
    }
}
