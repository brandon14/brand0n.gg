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

namespace Brand0nGG\Services\Status\Providers;

use Throwable;
use function microtime;
use function filter_var;
use InvalidArgumentException;
use const FILTER_VALIDATE_URL;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class WebsiteProvider.
 *
 * Website status provider. Will make an HTTP request using
 * {@link \Psr\Http\Client\ClientInterface} to see if a website
 * responds.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class WebsiteProvider implements StatusServiceProvider
{
    public const WEBSITE_STATUS_SLOW = 'SLOW';

    /**
     * PSR HTTP client.
     *
     * @var \Psr\Http\Client\ClientInterface
     */
    protected ClientInterface $httpClient;

    /**
     * PSR HTTP request factory.
     *
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    protected RequestFactoryInterface $requestFactory;

    /**
     * Route to hit to check website status.
     *
     * @var string
     */
    protected string $routeToPing;

    /**
     * How long a response should be under to consider status ok in ms.
     *
     * @var int
     */
    protected int $desiredTime;

    /**
     * Whether to add response headers to details.
     *
     * @var bool
     */
    protected bool $addHeaders;

    /**
     * Whether to add response time to details.
     *
     * @var bool
     */
    protected bool $addTime;

    /**
     * Name for additional website status details array key.
     *
     * @var string
     */
    protected string $detailKey;

    /**
     * Construct a new website status provider.
     *
     * @param \Psr\Http\Client\ClientInterface          $httpClient     PSR HTTP client instance
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory PSR HTTP request factory instance
     * @param string                                    $routeToPing    Route to hit using PSR HTTP client
     * @param int                                       $desiredTime    How long a response should be under to consider status ok in ms
     * @param bool                                      $addHeaders     Whether to add response headers to details
     * @param bool                                      $addTime        Whether to add response time to details
     * @param string                                    $detailKey      Name for additional website status details array key
     *
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings("BooleanArgumentFlag")
     *
     * @return void
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        string $routeToPing,
        int $desiredTime = 200,
        bool $addHeaders = true,
        bool $addTime = true,
        string $detailKey = 'details'
    ) {
        $this->setHttpClient($httpClient);
        $this->setRequestFactory($requestFactory);
        $this->setRouteToPing($routeToPing);
        $this->setDesiredTime($desiredTime);
        $this->setAddHeaders($addHeaders);
        $this->setAddTime($addTime);
        $this->setDetailKey($detailKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        try {
            $request = $this->requestFactory->createRequest('GET', $this->routeToPing);

            // Time response in seconds.
            $time = microtime(true);

            // Get the PSR-7 response from HTTP client.
            $response = $this->httpClient->sendRequest($request);

            $time = microtime(true) - $time;

            // Suppress psalm because we can't always assume every PSR standard library will completely adhere
            // to the interface documentation.
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $code = (int) $response->getStatusCode();
            // Get response details.

            if ($this->addHeaders) {
                $headers = $response->getHeaders();
            }

            $protocol = $response->getProtocolVersion();
            $reason = $response->getReasonPhrase();

            $status = [
                'status' => ($time * 1000) < $this->desiredTime ? StatusServiceProvider::STATUS_OK : self::WEBSITE_STATUS_SLOW,
                'details' => [
                    'response_code' => $code,
                    'reason'        => $reason,
                    'protocol'      => $protocol,
                ],
            ];

            if (isset($headers)) {
                $status['details']['headers'] = $headers;
            }

            if ($this->addTime) {
                $status['details']['response_time'] = $time;
            }

            return $status;
        } catch (Throwable $e) {
            // Swallow exceptions on purpose.
        }

        return ['status' => StatusServiceProvider::STATUS_ERROR];
    }


    /**
     * Get HTTP client interface instance.
     *
     * @return \Psr\Http\Client\ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Set HTTP client interface instance.
     *
     * @param \Psr\Http\Client\ClientInterface $httpClient PSR HTTP client interface
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get HTTP request factory interface instance.
     *
     * @return \Psr\Http\Message\RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Set HTTP request factory interface instance.
     *
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory PSR HTTP request factory instance
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    /**
     * Get route to ping to check for status.
     *
     * @return string
     */
    public function getRouteToPing(): string
    {
        return $this->routeToPing;
    }

    /**
     * Set route to ping to get status.
     *
     * @param string $routeToPing Route to ping
     *
     * @throws \InvalidArgumentException
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setRouteToPing(string $routeToPing): self
    {
        // Validate the URL to ping.
        if (filter_var($routeToPing, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException("Invalid URL [{$routeToPing}] provided.");
        }

        $this->routeToPing = $routeToPing;

        return $this;
    }

    /**
     * Get whether to add headers to status details.
     *
     * @return bool
     */
    public function isAddHeaders(): bool
    {
        return $this->addHeaders;
    }

    /**
     * Set whether to add headers to status details.
     *
     * @param bool $addHeaders Whether to add headers
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setAddHeaders(bool $addHeaders = true): self
    {
        $this->addHeaders = $addHeaders;

        return $this;
    }

    /**
     * Get whether to add response time to status details.
     *
     * @return bool
     */
    public function isAddTime(): bool
    {
        return $this->addTime;
    }

    /**
     * Set whether to add response time to status details.
     *
     * @param bool $addTime Whether to add response time
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setAddTime(bool $addTime = true): self
    {
        $this->addTime = $addTime;

        return $this;
    }

    /**
     * TODO: Undocumented getter.
     *
     * @return int
     */
    public function getDesiredTime(): int
    {
        return $this->desiredTime;
    }

    /**
     * TODO: Undocumented setter.
     *
     * @param int $desiredTime
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setDesiredTime(int $desiredTime = 200): self {
        $this->desiredTime = $desiredTime;

        return $this;
    }

    /**
     * TODO: Undocumented getter.
     *
     * @return string
     */
    public function getDetailKey(): string
    {
        return $this->detailKey;
    }

    /**
     * TODO: Undocumented setter.
     *
     * @param string $detailKey
     *
     * @return \Brand0nGG\Services\Status\Providers\WebsiteProvider
     */
    public function setDetailKey(string $detailKey = 'details'): self {
        $this->detailKey = $detailKey;

        return $this;
    }
}
