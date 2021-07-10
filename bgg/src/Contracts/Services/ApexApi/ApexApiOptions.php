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

namespace Brand0nGG\Contracts\Services\ApexApi;

use function in_array;
use function hash_algos;
use Brand0nGG\Contracts\Services\InvalidHashAlgorithmException;

/**
 * Class ApexApiOptions
 *
 * Defines the options available for the {@link \Brand0nGG\Contracts\Services\ApexApi\ApexApiService}
 * service class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class ApexApiOptions
{
    /**
     * Whether to cache the Apex API data or not.
     *
     * @var bool
     */
    private bool $isCacheEnabled;

    /**
     * How long to cache the Apex API data for.
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
     * API key.
     *
     * @var string
     */
    private string $apiKey;

    /**
     * Hash algorithm to use when making cache keys.
     *
     * @var string
     */
    private string $hashAlgo;

    /**
     * Constructs a new set of {@link \Brand0nGG\Contracts\Services\ApexApi\ApexApiService} options.
     *
     * @SuppressWarnings("BooleanArgumentFlag")
     *
     * @param string $apiKey          API key
     * @param bool   $isCacheEnabled  Whether caching is enabled
     * @param int    $cacheTtl        Cache time-to-live
     * @param string $cacheKey        Cache key
     * @param string $hashAlgo        Hash algorithm to use when making cache keys
     *
     * @throws \Brand0nGG\Contracts\Services\InvalidHashAlgorithmException
     *
     * @return void
     */
    public function __construct(
        string $apiKey,
        bool $isCacheEnabled = true,
        int $cacheTtl = 30,
        string $cacheKey = 'apexapi',
        string $hashAlgo = 'sha512'
    ) {
        $this->isCacheEnabled = $isCacheEnabled;
        $this->cacheTtl = $cacheTtl;
        $this->cacheKey = $cacheKey;
        $this->apiKey = $apiKey;

        if (! in_array($hashAlgo, hash_algos(), true)) {
            throw InvalidHashAlgorithmException::invalidAlgorithm($hashAlgo);
        }

        $this->hashAlgo = $hashAlgo;
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
     * Get api key option.
     *
     * @return string API key
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Get hash algorithm option
     *
     * @return string Hash algorithm string
     */
    public function getHashAlgo(): string
    {
        return $this->hashAlgo;
    }
}
