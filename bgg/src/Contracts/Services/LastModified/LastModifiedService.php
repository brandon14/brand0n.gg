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

use Carbon\CarbonInterface;

/**
 * Interface LastModifiedService.
 *
 * Last modified service interface. Allows registering
 * {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}
 * and will iterate through registered providers and return the
 * most recent timestamp.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface LastModifiedService
{
    /**
     * Adds a {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider} to the service.
     *
     * @param string                                                         $providerName Provider name
     * @param \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider $provider     Provider
     *
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return bool True iff provider was added, false otherwise
     */
    public function addProvider(string $providerName, LastModifiedTimeProvider $provider): bool;

    /**
     * Removes the named provider from the service.
     *
     * @param string $providerName Provider name
     *
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return bool True iff provider was removed, false otherwise
     */
    public function removeProvider(string $providerName): bool;

    /**
     * Get array of providers registered. Returns an array of
     * {@link \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider}.
     *
     * @return \Brand0nGG\Contracts\Services\LastModified\LastModifiedTimeProvider[] Array of providers
     */
    public function getProviders(): array;

    /**
     * Get array of registered providers names.
     *
     * @return string[] Array of provider names
     */
    public function getProviderNames(): array;

    /**
     * Gets the last modified time from a specific provider or if all is passed in, will
     * resolve timestamp from all providers.
     *
     * @param string|null $providerName Provider name
     *
     * @psalm-suppress InvalidThrow
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return \Carbon\CarbonInterface Last modified timestamp
     */
    public function getLastModifiedTime(?string $providerName = 'all'): CarbonInterface;

    /**
     * Gets the last modified time from an array of providers.
     *
     * @param string[] $providers Array of provider names
     *
     * @psalm-suppress InvalidThrow
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return \Carbon\CarbonInterface Last modified timestamp
     */
    public function getLastModifiedTimeByArray(array $providers): CarbonInterface;

    /**
     * Get the default timestamp format.
     *
     * @return string Date-time format
     */
    public function getDefaultTimestampFormat(): string;
}
