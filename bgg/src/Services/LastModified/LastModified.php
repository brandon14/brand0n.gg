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

namespace Brand0nGG\Services\LastModified;

use Throwable;
use Carbon\Carbon;
use function time;
use function count;
use function implode;
use function is_string;
use function array_filter;
use Carbon\CarbonInterface;
use Psr\SimpleCache\CacheInterface;
use Brand0nGG\Concerns\InteractsWithCache;
use Brand0nGG\Contracts\Services\CacheException;
use Brand0nGG\Contracts\Services\ProviderRegistrationException;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedOptions;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedService;
use Brand0nGG\Contracts\Services\CacheImplementationNeededException;
use Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider;

// TODO: Add getters and setters.

/**
 * Class LastModified.
 *
 * Last modified time service. Allows registering different
 * {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}
 * and will return the most recent timestamp from the providers.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LastModified implements LastModifiedService
{
    use InteractsWithCache;

    /**
     * Whether to cache the timestamp or not.
     *
     * @var bool
     */
    protected bool $isCacheEnabled;

    /**
     * How long to cache the last modified timestamp for.
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
     * Timestamp format.
     *
     * @var string
     */
    protected string $timestampFormat;

    /**
     * Associative array of 'name' => {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}.
     *
     * @psalm-var array<string, \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider>
     *
     * @var array
     */
    protected array $providers;

    /**
     * Constructs a LastModified service object.
     *
     * @param \Psr\SimpleCache\CacheInterface|null                           $cache   PSR-16 cache implementation
     * @param \Brand0nGG\Contracts\Services\LastModified\LastModifiedOptions|null $options Service options
     * @psalm-param array<string, \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider> $providers
     *
     * @param array $providers array of 'name' => {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}
     *
     * @throws \Brand0nGG\Contracts\Services\InvalidDateFormatException
     * @throws \Brand0nGG\Contracts\Services\CacheImplementationNeededException
     *
     * @return void
     */
    public function __construct(
        ?CacheInterface $cache = null,
        ?LastModifiedOptions $options = null,
        array $providers = []
    ) {
        // Ignore code coverage for this line. Its just setting a default set of options, so no need to really
        // write a unit test to cover this.
        // @codeCoverageIgnoreStart
        if ($options === null) {
            $options = new LastModifiedOptions();
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
        $this->timestampFormat = $options->getTimestampFormat();

        // Filter out invalid providers.
        // Psalm complains because with the annotated types, it "should" be a correct provider type, but
        // since its PHP, we filter out any incorrect providers.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $this->providers = array_filter(
            $providers,
            /**
             * Filter out providers that are not of instance {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}.
             *
             * @param mixed $provider {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}
             *
             * @return bool true iff it is an instance of {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider},
             *              false otherwise
             */
            static function ($provider): bool {
                return $provider instanceof LastModifiedTimeProvider;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider(string $providerName, LastModifiedTimeProvider $provider): bool
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
    public function getLastModifiedTime(?string $providerName = 'all'): CarbonInterface
    {
        // Treat null as fetching all providers.
        if ($providerName === null || $providerName === 'all') {
            return Carbon::createFromTimestamp(
                $this->resolveProviderArray(array_keys($this->providers), $this->cacheKey.'_all')
            );
        }

        $timestamp = $this->resolveTimestamp($providerName);

        // Prevent negative and future timestamps.
        if ($timestamp < 0 || $timestamp > time()) {
            $timestamp = time();
        }

        return Carbon::createFromTimestamp($timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModifiedTimeByArray(array $providers): CarbonInterface
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

        return Carbon::createFromTimestamp(
            $this->resolveProviderArray($providerNames, $this->cacheKey.'_'.implode('_', $providerNames))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTimestampFormat(): string
    {
        return $this->timestampFormat;
    }

    /**
     * Get last modified timestamp for an array of providers.
     *
     * @param string[] $providerNames Provider names
     * @param string   $cacheKey      Cache key
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return int Resolved timestamp
     */
    protected function resolveProviderArray(array $providerNames, string $cacheKey): int
    {
        // Check cache for this group of providers.
        if ($this->isCacheEnabled) {
            $timestamp = $this->normalizeCachedTimestamp($this->checkCacheForInt($cacheKey));

            if ($timestamp !== null) {
                return $timestamp;
            }
        }

        $timestamp = $this->resolveProviderTimestamps($providerNames);

        // Save in cache this provider group.
        if ($this->isCacheEnabled) {
            $this->saveIntInCache($cacheKey, $timestamp);
        }

        return $timestamp;
    }

    /**
     * Resolve latest timestamp from an array of provider names.
     *
     * @param string[] $providerNames Provider names
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return int Resolved timestamp
     */
    protected function resolveProviderTimestamps(array $providerNames): int
    {
        $timestamp = -1;

        // Resolve all providers, keeping track of the most recent one.
        foreach ($providerNames as $providerName) {
            $providerTimestamp = $this->resolveTimestamp($providerName);

            $timestamp = $providerTimestamp > $timestamp ? $providerTimestamp : $timestamp;
        }

        // Prevent negative and future timestamps.
        if ($timestamp < 0 || $timestamp > time()) {
            $timestamp = time();
        }

        return $timestamp;
    }

    /**
     * Resolve timestamp for a specific provider.
     *
     * @param string $providerName Provider name
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return int Resolved timestamp
     */
    protected function resolveTimestamp(string $providerName): int
    {
        // Invalid (not registered) provider.
        if (! isset($this->providers[$providerName])) {
            throw ProviderRegistrationException::noProviderRegistered($providerName);
        }

        $cacheKey = $this->cacheKey.'_'.$providerName;

        // Check the cache for the provider if enabled.
        if ($this->isCacheEnabled) {
            $timestamp = $this->normalizeCachedTimestamp($this->checkCacheForInt($cacheKey));

            if ($timestamp !== null) {
                return $timestamp;
            }
        }

        $timestamp = $this->providers[$providerName]->getLastModifiedTime();

        // Cache status for this provider.
        if ($this->isCacheEnabled) {
            $this->saveIntInCache($cacheKey, $timestamp);
        }

        return $timestamp;
    }

    /**
     * Will return null for a timestamp equal to 0, which can be returned from the
     * cache when converted to an int if invalid data is returned.
     *
     * @param int|null $timestamp Timestamp (can be null)
     *
     * @return int|null
     */
    protected function normalizeCachedTimestamp(?int $timestamp): ?int
    {
        // Invalid data from cache can return 0, so transform it to null in that case;
        return $timestamp === 0 ? null : $timestamp;
    }
}
