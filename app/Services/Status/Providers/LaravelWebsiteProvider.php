<?php

namespace App\Services\Status\Providers;

use Brand0nGG\Services\Status\Providers\WebsiteProvider;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\UrlGenerator;
use Psr\Http\Message\RequestFactoryInterface;

/**
 * Class LaravelWebsiteProvider
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LaravelWebsiteProvider extends WebsiteProvider
{
    public function __construct(Client $guzzle, RequestFactoryInterface $requestFactory, Repository $config, UrlGenerator $urlGenerator)
    {
        $routeToPing = $urlGenerator->route((string) $config->get('status.website.route_to_ping'));
        $desiredTime = (int) $config->get('status.website.desired_time', 200);
        $addHeaders = (bool) $config->get('status.website.add_headers', true);
        $addTime = (bool) $config->get('status.website.add_time', true);
        $detailKey = (string) $config->get('status.website.detail_key', 'details');

        parent::__construct(
            $guzzle,
            $requestFactory,
            $routeToPing,
            $desiredTime,
            $addHeaders,
            $addTime,
            $detailKey
        );
    }
}
