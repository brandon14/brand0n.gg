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

namespace Brand0nGG\Contracts\Services;

use Throwable;
use RuntimeException;

/**
 * Class CacheException.
 *
 * Exception thrown when the services cannot store or
 * retrieve from the {@link \Psr\SimpleCache\CacheInterface}.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class CacheException extends RuntimeException
{
    /**
     * Creates a new {@link \Brand0nGG\Contracts\Services\CacheException} instance for a
     * cache save failure when saving a timestamp to cache.
     *
     * @param string          $cacheKey Cache key
     * @param int             $code     Error code
     * @param \Throwable|null $previous Previous exception
     *
     * @return \Brand0nGG\Contracts\Services\CacheException
     */
    public static function createForIntSaveFailure(
        string $cacheKey = 'last_modified',
        int $code = 0,
        ?Throwable $previous = null
    ): self {
        return new self("Unable to save timestamp in cache for cache key [{$cacheKey}].", $code, $previous);
    }

    /**
     * Creates a new {@link \Brand0nGG\Contracts\Services\CacheException} instance for a
     * cache save failure when trying to save a status array in the cache.
     *
     * @param string          $cacheKey Cache key
     * @param int             $code     Error code
     * @param \Throwable|null $previous Previous exception
     *
     * @return \Brand0nGG\Contracts\Services\CacheException
     */
    public static function createForArraySaveFailure(
        string $cacheKey = 'statuses',
        int $code = 0,
        ?Throwable $previous = null
    ): self {
        return new self("Unable to save status in cache for cache key [{$cacheKey}].", $code, $previous);
    }

    /**
     * Creates a new {@link \Brand0nGG\Contracts\Services\CacheException} from an
     * existing {@link \Throwable}.
     *
     * @param \Throwable $exception Original exception instance
     *
     * @return \Brand0nGG\Contracts\Services\CacheException
     */
    public static function createFromException(Throwable $exception): self
    {
        return new self($exception->getMessage(), (int) $exception->getCode(), $exception);
    }
}
