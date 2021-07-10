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

namespace Brand0nGG\Services\Status;

use Throwable;
use function count;
use function implode;
use function is_array;
use function is_string;
use function serialize;
use function array_keys;
use function unserialize;
use function array_filter;
use Psr\SimpleCache\CacheInterface;
use Brand0nGG\Concerns\InteractsWithCache;
use Brand0nGG\Contracts\Services\CacheException;
use Brand0nGG\Contracts\Services\Status\StatusOptions;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;
use Brand0nGG\Contracts\Services\ProviderRegistrationException;
use Brand0nGG\Contracts\Services\CacheImplementationNeededException;
use Brand0nGG\Contracts\Services\Status\StatusService as StatusServiceInterface;

// TODO: Add getters and setters.

/**
 * Class StatusService.
 *
 * Status service. Allows registering different
 * {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider}
 * and will return statuses from those providers.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class StatusService implements StatusServiceInterface
{
    use InteractsWithCache;

    /**
     * Whether to cache statuses as they are obtained.
     *
     * @var bool
     */
    protected bool $isCacheEnabled;

    /**
     * How long to cache the status for.
     *
     * @var int
     */
    protected int $cacheTtl;

    /**
     * Cache key.
     *
     * @var string
     */
    protected string $cacheKey;

    /**
     * Associative array of 'name' => {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider}.
     *
     * @psalm-var array<string, \Brand0nGG\Contracts\Services\Status\StatusServiceProvider>
     *
     * @var array
     */
    protected array $providers;

    /**
     * Construct a new status service.
     *
     * @param \Psr\SimpleCache\CacheInterface|null               $cache   PSR-16 cache implementation
     * @param \Brand0nGG\Contracts\Services\Status\StatusOptions|null $options Service options
     * @psalm-param array<string, \Brand0nGG\Contracts\Services\Status\StatusServiceProvider> $providers
     *
     * @param array $providers array of 'name' => {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider} pairs
     *
     * @throws \Brand0nGG\Contracts\Services\CacheImplementationNeededException
     *
     * @return void
     */
    public function __construct(
        ?CacheInterface $cache = null,
        ?StatusOptions $options = null,
        array $providers = []
    ) {
        // Ignore code coverage for this line. Its just setting a default set of options, so no need to really
        // write a unit test to cover this.
        // @codeCoverageIgnoreStart
        if ($options === null) {
            $options = new StatusOptions();
        }
        // @codeCoverageIgnoreEnd

        // Make sure a valid cache implementation is provided if caching is enabled.
        if ($cache === null && $options->isCacheEnabled()) {
            throw CacheImplementationNeededException::cacheImplementationNeeded();
        }

        // Set service options.
        $this->cache = $cache;
        $this->isCacheEnabled = $options->isCacheEnabled();
        $this->cacheTtl = $options->getCacheTtl();
        $this->cacheKey = $options->getCacheKey();

        // Filter out invalid providers.
        // Psalm complains because with the annotated types, it "should" be a correct provider type, but
        // since its PHP, we filter out any incorrect providers.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->providers = array_filter(
            $providers,
            /**
             * Filter out providers that are not of instance {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider}.
             *
             * @param mixed $provider {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider}
             *
             * @return bool true iff it is an instance of {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider},
             *              false otherwise
             */
            static function ($provider): bool {
                return $provider instanceof StatusServiceProvider;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider(string $providerName, StatusServiceProvider $provider): bool
    {
        if (isset($this->providers[$providerName])) {
            throw ProviderRegistrationException::providerAlreadyRegistered($providerName);
        }

        $this->providers[$providerName] = $provider;

        return isset($this->providers[$providerName]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeProvider(string $providerName): bool
    {
        if (! isset($this->providers[$providerName])) {
            throw ProviderRegistrationException::noProviderRegistered($providerName);
        }

        unset($this->providers[$providerName]);

        return ! isset($this->providers[$providerName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders(): array
    {
        return array_values($this->providers);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderNames(): array
    {
        return array_keys($this->providers);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(?string $providerName = 'all'): array
    {
        // Treat null as fetching all provider statuses.
        if ($providerName === null || $providerName === 'all') {
            return $this->resolveProviderArray(array_keys($this->providers), $this->cacheKey.'_all');
        }

        return [$providerName => $this->resolveStatus($providerName)];
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusByArray(array $providers): array
    {
        // Must provide a list of providers to resolve.
        if (count($providers) === 0) {
            throw ProviderRegistrationException::noProvidersSpecified();
        }

        // Filter out provider array to only allow non-empty strings. It's PHP
        // so deal with it.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $providerNames = array_filter(
            $providers,
            /**
             * Determine if provider name is a string and not empty.
             *
             * @param mixed $string Provider name
             *
             * @return bool true iff param is a string and not empty, false otherwise
             */
            static function ($string): bool {
                return is_string($string) && $string !== '';
            }
        );

        return $this->resolveProviderArray($providerNames, $this->cacheKey.'_'.implode('_', $providerNames));
    }

    /**
     * Resolve statuses of an array of provider names.
     *
     * @param string[] $providerNames Array of provider names
     * @param string   $cacheKey      Cache key
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @psalm-return array<mixed, mixed>
     *
     * @return array Resolved provider statuses
     */
    protected function resolveProviderArray(array $providerNames, string $cacheKey): array
    {
        $statuses = [];

        // Check the cache for this particular grouping of providers.
        if ($this->isCacheEnabled) {
            $status = $this->checkCache($cacheKey);

            if ($status !== null) {
                return $status;
            }
        }

        // Resolve each provider and store in our array.
        foreach ($providerNames as $provider) {
            $statuses[$provider] = $this->resolveStatus($provider);
        }

        // Cache statuses for this status provider group.
        if ($this->isCacheEnabled) {
            $this->saveInCache($cacheKey, $statuses);
        }

        return $statuses;
    }

    /**
     * Resolves a status for a specific provider.
     *
     * @param string $providerName Provider name
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @psalm-return array<mixed, mixed>
     *
     * @return array Resolved provider status
     */
    protected function resolveStatus(string $providerName): array
    {
        // Invalid (not registered) provider.
        if (! isset($this->providers[$providerName])) {
            throw ProviderRegistrationException::noProviderRegistered($providerName);
        }

        $cacheKey = $this->cacheKey.'_'.$providerName;

        // Check the cache for the provider if enabled.
        if ($this->isCacheEnabled) {
            $status = $this->checkCacheForArray($cacheKey);

            if ($status !== null) {
                return $status;
            }
        }

        $status = $this->providers[$providerName]->getStatus();

        // Cache status for this provider.
        if ($this->isCacheEnabled) {
            $this->saveArrayInCache($cacheKey, $status);
        }

        return $status;
    }
}
