<?php

namespace App\Services\Status\Providers;

use Brand0nGG\Services\Status\Providers\PhpRedisProvider;
use Illuminate\Config\Repository;
use Illuminate\Redis\RedisManager;

/**
 * Class LaravelPhpRedisProvider
 *
 * TODO: Undocumented class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class LaravelPhpRedisProvider extends PhpRedisProvider
{
    public function __construct(RedisManager $redis, Repository $config)
    {
        $connection = $config->get('status.redis.connection_name');
        $detailKey = $config->get('status.redis.detail_key');
        $infoCommands = $config->get('status.redis.info_commands');
        $excludedKeys = $config->get('status.redis.excluded_keys');

        $redis = $redis->connection($connection)->client();

        parent::__construct($redis, $detailKey, $infoCommands, $excludedKeys);
    }
}
