<?php

namespace App\Services\ApexApi;

use Brand0nGG\Contracts\Services\ApexApi\ApexApiOptions;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiServiceException;
use GuzzleHttp\ClientInterface;
use Brand0nGG\Services\ApexApi\ApexApi as ApexApiService;
use Psr\SimpleCache\CacheInterface;
use Throwable;

/**
 * Class ApexApi
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class ApexApi extends ApexApiService
{
    private ClientInterface $http;

    public function __construct(ClientInterface $http, ApexApiOptions $options, ?CacheInterface $cache = null)
    {
        parent::__construct($cache, $options);

        $this->http = $http;
    }

    /**
     * @inheritDoc
     */
    protected function makeJsonApiCall(string $url): string
    {
        try {
            return (string) $this->http->request('GET', $url)->getBody();
        } catch (Throwable $e) {
            throw new ApexApiServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
