<?php

namespace App\Services\Status\Providers;

use Brand0nGG\Services\Status\Providers\OpcacheProvider;
use Illuminate\Config\Repository;

/**
 * Class LaravelOpcacheProvider
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LaravelOpcacheProvider extends OpcacheProvider
{
    public function __construct(Repository $config)
    {
        $detailKey = (string) $config->get('status.opcache.detail_key', 'details');

        parent::__construct($detailKey);
    }
}
