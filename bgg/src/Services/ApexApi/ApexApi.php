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

namespace Brand0nGG\Services\ApexApi;

use JsonException;
use function in_array;
use function json_decode;
use function str_contains;
use function rawurlencode;
use Psr\SimpleCache\CacheInterface;
use Brand0nGG\Concerns\InteractsWithCache;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiOptions;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiService;
use Brand0nGG\Contracts\Services\HashCreationFailureException;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiDecodeException;
use Brand0nGG\Contracts\Services\ApexApi\InvalidPlatformException;
use Brand0nGG\Contracts\Services\CacheImplementationNeededException;

/**
 * Apex API service implementation. Allows interaction with the Apex API via PHP.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
abstract class ApexApi implements ApexApiService
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
     * API key.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * Hash algorithm to use when making cache keys.
     *
     * @var string
     */
    protected string $hashAlgo;

    /**
     * Constructs new ApexApi class to interact with the ApexAPI.
     *
     * @see https://apexlegendsapi.com
     *
     * @param \Psr\SimpleCache\CacheInterface|null                 $cache
     * @param \Brand0nGG\Contracts\Services\ApexApi\ApexApiOptions $options
     */
    public function __construct(?CacheInterface $cache, ApexApiOptions $options)
    {
        // Make sure a valid cache implementation is provided if caching is enabled.
        if ($cache === null && $options->isCacheEnabled()) {
            throw CacheImplementationNeededException::cacheImplementationNeeded();
        }

        // Set service options.
        $this->cache = $cache;
        $this->isCacheEnabled = $options->isCacheEnabled();
        $this->cacheTtl = $options->getCacheTtl();
        $this->cacheKey = $options->getCacheKey();

        $this->apiKey = $options->getApiKey();
        $this->hashAlgo = $options->getHashAlgo();
    }

    /**
     * Gets the API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Sets the API key.
     *
     * @param string $apiKey API key
     *
     * @return $this
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Method to make the actual API call and return the JSON string. This is abstracted
     * so it is easy to test and swap concrete implementations if needed.
     *
     * @param string $url API URL to target, will need to contain all params except the API key.
     *
     * @throws \Brand0nGG\Contracts\Services\ApexApi\ApexApiServiceException
     *
     * @return string
     */
    abstract protected function makeJsonApiCall(string $url): string;

    /**
     * {@inerhitDoc}
     */
    public function getPlayerStats(int $uid, string $platform = 'PC'): array
    {
        $this->validatePlatform($platform);

        return $this->resolveApiData(
            ApexApiService::APEX_API_PLAYER_STATS_URL."&platform={$platform}&uid={$uid}",
            'player_stats_uid_'.$this->getHashedCacheKey("{$uid}_{$platform}")
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function getPlayerStatsByName(string $userName, string $platform = 'PC'): array
    {
        $userName = rawurlencode($userName);

        $this->validatePlatform($platform);

        return $this->resolveApiData(
            ApexApiService::APEX_API_PLAYER_STATS_URL."&platform={$platform}&player={$userName}",
            'player_stats_'.$this->getHashedCacheKey("{$userName}_{$platform}")
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function getMapRotation(): array
    {
        return $this->resolveApiData(
            ApexApiService::APEX_API_MAP_ROTATION_URL,
            'map_rotation'
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function getNews(string $lang = 'en-us'): array
    {
        $lang = rawurlencode($lang);

        return $this->resolveApiData(
            ApexApiService::APEX_API_NEWS_URL."?lang={$lang}",
            'news_'.$this->getHashedCacheKey($lang)
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function getServerStatus(): array
    {
        return $this->resolveApiData(
            ApexApiService::APEX_API_SERVER_STATUS_URL,
            'server_status'
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function getOriginPlayer(string $userName): array
    {
        $userName = rawurlencode($userName);

        return $this->resolveApiData(
            ApexApiService::APEX_API_ORIGIN_URL."?player={$userName}",
            'origin_player_'.$this->getHashedCacheKey($userName)
        );
    }

    /**
     * {@inerhitDoc}
     */
    public function nameToUid(string $userName, string $platform = 'PC'): array
    {
        $userName = rawurlencode($userName);

        $this->validatePlatform($platform);

        return $this->resolveApiData(
            ApexApiService::APEX_API_NAME_TO_UID_URL."&platform={$platform}&player={$userName}",
            'name_to_uid_'.$this->getHashedCacheKey("{$userName}_{$platform}")
        );
    }

    /**
     * Get the hashed version of the cache key.
     *
     * @param string $string Cache key to hash
     *
     * @return string
     */
    protected function getHashedCacheKey(string $string): string
    {
        $cacheHash = hash($this->hashAlgo, $string);

        if ($cacheHash === false) {
            throw HashCreationFailureException::createForHashFailure($this->hashAlgo, 'Hash function returned false.');
        }

        return $cacheHash;
    }

    /**
     * Method to make the API call and parse the JSON results. Takes a fully formed API
     * URL including the API key, and will return an array of the API data.
     *
     * @param string $url
     *
     * @throws \Brand0nGG\Contracts\Services\ApexApi\ApexApiDecodeException
     * @throws \Brand0nGG\Contracts\Services\ApexApi\ApexApiServiceException
     *
     * @return array
     */
    protected function getApiResults(string $url): array
    {
        $json = $this->makeJsonApiCall($url);

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ApexApiDecodeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Validates a given platform to ensure it is valid.
     *
     * @param string $platform Platform
     */
    protected function validatePlatform(string $platform)
    {
        if (! in_array($platform, ApexApiService::VALID_PLATFORMS)) {
            throw InvalidPlatformException::invalidPlatform($platform);
        }
    }

    /**
     * Will take a URL and add in the API key to the URL params.
     *
     * @param string $url Current API URL
     *
     * @return string
     */
    protected function addApiKey(string $url): string
    {
        return $url.(str_contains($url, '?') ? '&auth=' : '?auth=').$this->getApiKey();
    }

    /**
     * Resolves the API data by first checking for cached data if caching is enabled, then
     * will make the API call and cache the data if enabled.
     *
     * @param string $url      Fully formed API URL without API key
     * @param string $cacheKey Cache key
     *
     * @return array
     */
    protected function resolveApiData(string $url, string $cacheKey): array
    {
        $key = $this->cacheKey."_{$cacheKey}";

        // Check the cache for the provider if enabled.
        if ($this->isCacheEnabled) {
            $apiData = $this->checkCacheForArray($key);

            if ($apiData !== null) {
                return $apiData;
            }
        }

        $apiData = $this->getApiResults($this->addApiKey($url));

        // Cache status for this provider.
        if ($this->isCacheEnabled) {
            $this->saveArrayInCache($key, $apiData);
        }

        return $apiData;
    }
}
