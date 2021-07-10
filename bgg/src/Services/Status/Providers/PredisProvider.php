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
use Predis\Response\Status;
use Predis\ClientInterface as Predis;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class PredisProvider.
 *
 * Predis status provider. Allows pining a redis cache database via
 * {@link \Predis\Predis} to check the status of the database.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class PredisProvider extends AbstractRedisProvider implements StatusServiceProvider
{
    /**
     * Predis client instance.
     *
     * @var \Predis\ClientInterface
     */
    protected Predis $redis;

    /**
     * Construct a new predis status provider.
     *
     * @param \Predis\ClientInterface $redis        Predis instance
     * @param string                  $detailKey    Name of detail key returned with the status array
     * @param string[]                $infoCommands Array of parameters to pass to the info command to obtain various Redis server stats
     * @param string[]                $excludedKeys Stats key names to be excluded from the final information array
     *
     * @return void
     */
    public function __construct(
        Predis $redis,
        string $detailKey = 'details',
        array $infoCommands = [],
        array $excludedKeys = []
    ) {
        $this->setRedis($redis);

        parent::__construct($detailKey, $infoCommands, $excludedKeys);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        try {
            return $this->getRedisStatus();
        } catch (Throwable $e) {
            // Swallow exceptions on purpose.
        }

        return ['status' => StatusServiceProvider::STATUS_ERROR];
    }

    /**
     * Get PhpRedis instance.
     *
     * @return \Predis\ClientInterface
     */
    public function getRedis(): Predis
    {
        return $this->redis;
    }

    /**
     * Set PhpRedis instance.
     *
     * @param \Predis\ClientInterface $redis
     *
     * @return \Brand0nGG\Services\Status\Providers\PredisProvider
     */
    public function setRedis(Predis $redis): self
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeInfoCommand(string $command): array
    {
        return $this->redis->info($command);
    }

    /**
     * {@inheritdoc}
     */
    protected function ping(): bool
    {
        $command = $this->redis->ping();

        return $command instanceof Status && $command->getPayload() === 'PONG';
    }
}
