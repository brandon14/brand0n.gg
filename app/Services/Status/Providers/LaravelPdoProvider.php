<?php

namespace App\Services\Status\Providers;

use Brand0nGG\Services\Status\Providers\PdoProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;

/**
 * Class LaravelPdoProvider
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LaravelPdoProvider extends PdoProvider
{
    public function __construct(DatabaseManager $dbManager, Repository $config)
    {
        $connection = (string) $config->get('status.pdo.connection', 'default');
        $pdo = $dbManager->connection($connection)->getPdo();
        $pingQuery = $config->get('status.pdo.ping_query');
        $detailsQuery = $config->get('status.pdo.details_query', null);

        parent::__construct($pdo, $pingQuery, $detailsQuery);
    }
}
