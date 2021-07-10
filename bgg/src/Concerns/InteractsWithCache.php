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

namespace Brand0nGG\Concerns;

use Brand0nGG\Contracts\Services\CacheException;
use Throwable;
use function is_array;
use function serialize;
use function is_string;
use function unserialize;
use Psr\SimpleCache\CacheInterface;

/**
 * Trait InteractsWithCache
 *
 * Trait to consolidate all of the cache interactions for the various services and providers.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
trait InteractsWithCache
{
    /**
     * Application cache store.
     *
     * @var \Psr\SimpleCache\CacheInterface|null
     */
    protected ?CacheInterface $cache;

    /**
     * Check the cache for the given key and return it if it exists, otherwise return null.
     *
     * @psalm-suppress PossiblyNullReference
     *
     * @param string $cacheKey Cache key
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     *
     * @psalm-return array<mixed, mixed>|null
     *
     * @return array|null Status array if present, null iff no cache hit
     */
    protected function checkCacheForArray(string $cacheKey): ?array
    {
        try {
            // Check the cache.
            // PSR's throws annotation are incorrect because the base CacheException is an interface.
            /** @psalm-suppress MissingThrowsDocblock */
            if ($this->cache->has($cacheKey)) {
                return $this->resolveCachedArray($cacheKey);
            }
        } catch (Throwable $exception) {
            throw CacheException::createFromException($exception);
        }

        return null;
    }

    /**
     * Resolve cached array of data from cache. If no cache entry is found or cannot be resolve, null will
     * be returned.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress InvalidThrow
     *
     * @param string $cacheKey Cache key
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @psalm-return array<mixed, mixed>|null
     *
     * @return array|null Data array if present, null iff no cache hit
     */
    protected function resolveCachedArray(string $cacheKey): ?array
    {
        /** @var string|null */
        $cache = $this->cache->get($cacheKey, null);

        // Nothing was returned from the cache, return null.
        if ($cache === null) {
            return null;
        }

        // We don't have a serialized string, so return null since we can't
        // serialize it.
        // Since we can't guarantee the return type from the cache, this explicit check is still
        // needed even though we say it will either be a string or null since PSR's cache get return
        // type is very loose (mixed).
        if (! is_string($cache)) {
            return null;
        }

        // Unserialize what is returned from the cache. Also it should
        // only ever be an array, so no unserializing classes (RCE anyone?)
        /** @var array */
        $apiData = unserialize($cache, ['allowed_classes' => false]);

        // If the unserialization failed, or it does not result in an array, return
        // null.
        // Psalm complains, but because this is PHP, we want to be sure we return either
        // an array or null to abide by the documented return type.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        return ! is_array($apiData) ? null : $apiData;
    }

    /**
     * Saves array of data in cache.
     *
     * @psalm-suppress PossiblyNullReference
     *
     * @psalm-param array<mixed, mixed> $data
     *
     * @param string $cacheKey Cache key
     * @param array  $data     Data array to store in cache
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     */
    protected function saveArrayInCache(string $cacheKey, array $data): void
    {
        try {
            // Attempt to save cache item. If that fails, throw an exception.
            // PSR's throws annotation are incorrect because the base CacheException is an interface.
            /** @psalm-suppress MissingThrowsDocblock */
            $saved = $this->cache->set($cacheKey, serialize($data), $this->cacheTtl);

            // Failed to save status, throw an exception.
            if ($saved === false) {
                throw CacheException::createForArraySaveFailure($cacheKey);
            }
        } catch (Throwable $exception) {
            throw CacheException::createFromException($exception);
        }
    }

     /**
      * Check the cache for the given key (integer data) and return it iff it exists, otherwise return null.
      *
      * @psalm-suppress PossiblyNullReference
      *
      * @param string $cacheKey Cache key
      *
      * @throws \Brand0nGG\Contracts\Services\CacheException
      * @throws \Psr\SimpleCache\InvalidArgumentException
      *
      * @return int|null Resolved int iff found, null otherwise
      */
    protected function checkCacheForInt(string $cacheKey): ?int
    {
        try {
            // Check the cache.
            // PSR's throws annotation are incorrect because the base CacheException is an interface.
            /** @psalm-suppress MissingThrowsDocblock */
            if ($this->cache->has($cacheKey)) {
                // Coerce cache value into an integer.
                return (int) $this->cache->get($cacheKey);
            }
        } catch (Throwable $exception) {
            throw CacheException::createFromException($exception);
        }

        return null;
    }

     /**
      * Saves integer in cache.
      *
      * @psalm-suppress PossiblyNullReference
      *
      * @param string $cacheKey  Cache key
      * @param int    $int       Integer to save in cache
      *
      * @throws \Psr\SimpleCache\InvalidArgumentException
      * @throws \Brand0nGG\Contracts\Services\CacheException
      */
    protected function saveIntInCache(string $cacheKey, int $int): void
    {
        try {
            // Make sure cache entry was saved. If not throw a cache exception.
            // PSR's throws annotation are incorrect because the base CacheException is an interface.
            /** @psalm-suppress MissingThrowsDocblock */
            $saved = $this->cache->set($cacheKey, $int, $this->cacheTtl);

            if ($saved === false) {
                throw CacheException::createForIntSaveFailure($cacheKey);
            }
        } catch (Throwable $exception) {
            throw CacheException::createFromException($exception);
        }
    }
}
