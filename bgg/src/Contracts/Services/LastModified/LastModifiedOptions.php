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

namespace Brand0nGG\Contracts\Services\LastModified;

use function date;
use Brand0nGG\Contracts\Services\InvalidDateFormatException;

/**
 * Class LastModifiedOptions.
 *
 * Last modified time service options. This defines the options available for
 * the {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedService} service.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class LastModifiedOptions
{
    /**
     * Whether to cache the timestamp or not.
     *
     * @var bool
     */
    private bool $isCacheEnabled;

    /**
     * How long to cache the last modified timestamp for.
     *
     * @var int
     */
    private int $cacheTtl;

    /**
     * Cache key.
     *
     * @var string
     */
    private string $cacheKey;

    /**
     * Default timestamp format.
     *
     * @var string
     */
    private string $timestampFormat;

    /**
     * Constructs a new set of {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedService} options.
     *
     * @SuppressWarnings("BooleanArgumentFlag")
     *
     * @param bool   $isCacheEnabled  Whether caching is enabled
     * @param int    $cacheTtl        Cache time-to-live
     * @param string $cacheKey        Cache key
     * @param string $timestampFormat Timestamp format
     *
     * @throws \Brand0nGG\Contracts\Services\InvalidDateFormatException
     *
     * @return void
     */
    public function __construct(
        bool $isCacheEnabled = true,
        int $cacheTtl = 30,
        string $cacheKey = 'last_modified',
        string $timestampFormat = 'F jS, Y \a\t h:i:s A T'
    ) {
        $this->isCacheEnabled = $isCacheEnabled;
        $this->cacheTtl = $cacheTtl;
        $this->cacheKey = $cacheKey;
        $this->timestampFormat = $timestampFormat;

        if (! date($this->timestampFormat)) {
            throw InvalidDateFormatException::invalidFormat($this->timestampFormat);
        }
    }

    /**
     * Get whether caching is enabled.
     *
     * @return bool Whether caching is enabled
     */
    public function isCacheEnabled(): bool
    {
        return $this->isCacheEnabled;
    }

    /**
     * Get cache TTL option.
     *
     * @return int Cache time-to-live
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Get cache key option.
     *
     * @return string Cache key
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * Get timestamp format option.
     *
     * @return string Timestamp format
     */
    public function getTimestampFormat(): string
    {
        return $this->timestampFormat;
    }
}
