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

namespace Brand0nGG\Contracts\Services\Status;

/**
 * Interface StatusService.
 *
 * System status service. Allows registering multiple status providers (i.e. database, cache, services, etc)
 * and provides an interface to get the status of these providers.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface StatusService
{
    /**
     * Adds a {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider} to the service.
     *
     * @param string                                                $providerName Provider name
     * @param \Brand0nGG\Contracts\Services\Status\StatusServiceProvider $provider     Provider
     *
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return bool True iff the provider was added, false otherwise
     */
    public function addProvider(string $providerName, StatusServiceProvider $provider): bool;

    /**
     * Removes the named provider from the service.
     *
     * @param string $providerName Provider name
     *
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @return bool True iff the provider was removed, false otherwise
     */
    public function removeProvider(string $providerName): bool;

    /**
     * Get array of providers registered. Returns an array of
     * {@link \Brand0nGG\Contracts\Services\Status\StatusServiceProvider}.
     *
     * @return \Brand0nGG\Contracts\Services\Status\StatusServiceProvider[] Array of providers
     */
    public function getProviders(): array;

    /**
     * Get array of registered providers names.
     *
     * @return string[] Array of provider names
     */
    public function getProviderNames(): array;

    /**
     * Get the status for a provider (or all providers if string 'all' or  no param is passed in) or
     * list of providers.
     *
     * @param string|null $providerName Provider name
     *
     * @psalm-suppress InvalidThrow
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @psalm-return array<mixed, mixed>
     *
     * @return array Array of provider statuses
     */
    public function getStatus(?string $providerName = 'all'): array;

    /**
     * Get the status for an array of provider names.
     *
     * @psalm-param string[] $providers
     *
     * @param array $providers Array of providers
     *
     * @psalm-suppress InvalidThrow
     *
     * @throws \Brand0nGG\Contracts\Services\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Brand0nGG\Contracts\Services\ProviderRegistrationException
     *
     * @psalm-return array<mixed, mixed>
     *
     * @return array Array of provider statuses
     */
    public function getStatusByArray(array $providers): array;
}
