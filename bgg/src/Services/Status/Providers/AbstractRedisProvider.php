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

use function count;
use function is_string;
use function array_merge;
use function array_filter;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use function iterator_to_array;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class AbstractRedisProvider.
 *
 * Class to abstract out common logic between different PHP based Redis providers.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
abstract class AbstractRedisProvider
{
    /**
     * Name of detail key returned with the status array.
     *
     * @var string
     */
    protected string $detailKey;

    /**
     * Array of parameters to pass to the info command to obtain various Redis server stats.
     *
     * @see https://redis.io/commands/info
     *
     * @var string[]
     */
    protected array $infoCommands;

    /**
     * Stats key names to be excluded from the final information array.
     *
     * @see https://redis.io/commands/info
     *
     * @var string[]
     */
    protected array $excludedKeys;

    public function __construct(
        string $detailKey = 'details',
        array $infoCommands = [],
        array $excludedKeys = []
    ) {
        $this->setDetailKey($detailKey);
        $this->setInfoCommands($infoCommands);
        $this->setExcludedKeys($excludedKeys);
    }

    /**
     * Get detail key string.
     *
     * @return string
     */
    public function getDetailKey(): string
    {
        return $this->detailKey;
    }

    /**
     * Set detail key string.
     *
     * @param string $detailKey
     *
     * @return \Brand0nGG\Services\Status\Providers\AbstractRedisProvider
     */
    public function setDetailKey(string $detailKey = 'details'): self
    {
        $this->detailKey = $detailKey;

        return $this;
    }

    /**
     * Get list of info command params to execute.
     *
     * @see https://redis.io/commands/info
     *
     * @return string[]
     */
    public function getInfoCommands(): array
    {
        return $this->infoCommands;
    }

    /**
     * Set list of info command params to execute.
     *
     * @see https://redis.io/commands/info
     *
     * @param string[] $infoCommands
     *
     * @return \Brand0nGG\Services\Status\Providers\AbstractRedisProvider
     */
    public function setInfoCommands(array $infoCommands): self
    {
        // Filter out command parameter array to only allow non-empty strings. It's PHP
        // so deal with it.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $infoCommands = array_filter(
            $infoCommands,
            /**
             * Determine if command parameter name is a string and not empty.
             *
             * @param mixed $string Command parameter name
             *
             * @return bool true iff param is a string and not empty, false otherwise
             */
            static function ($string): bool {
                return is_string($string) && $string !== '';
            }
        );
        $this->infoCommands = $infoCommands;

        return $this;
    }

    /**
     * Get list of excluded info keys that are returned in the final status array.
     *
     * @see https://redis.io/commands/info
     *
     * @return string[]
     */
    public function getExcludedKeys(): array
    {
        return $this->excludedKeys;
    }

    /**
     * Set the list of info keys to exclude when pulling Redis information.
     *
     * @see https://redis.io/commands/info
     *
     * @param string[] $excludedKeys
     *
     * @return \Brand0nGG\Services\Status\Providers\AbstractRedisProvider
     */
    public function setExcludedKeys(array $excludedKeys): self
    {
        // Filter out excluded keys array to only allow non-empty strings. It's PHP
        // so deal with it.
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        $excludedKeys = array_filter(
            $excludedKeys,
            /**
             * Determine if excluded key name is a string and not empty.
             *
             * @param mixed $string Excluded key name
             *
             * @return bool true iff param is a string and not empty, false otherwise
             */
            static function ($string): bool {
                return is_string($string) && $string !== '';
            }
        );
        $this->excludedKeys = $excludedKeys;

        return $this;
    }

    /**
     * Method to send the info with param command to the server.
     *
     * @see https://redis.io/commands/info
     *
     * @param string $command Redis info command parameter (i.e. 'ALL', etc).
     *
     * @throws \Throwable Can throw exception if commands fail
     *
     * @return array Array of various Redis 'INFO' details.
     */
    abstract protected function executeInfoCommand(string $command): array;

    /**
     * Method to ping the redis server and return true or false.
     *
     * @throws \Throwable Can throw exception if commands fail
     *
     * @return bool True iff the server responds, false otherwise.
     */
    abstract protected function ping(): bool;

    /**
     * Get the Redis details via the 'INFO' command.
     *
     * @throws \Throwable Can throw exception if commands fail
     *
     * @return array Array of various Redis 'INFO' details.
     */
    protected function getRedisStatus(): array
    {
        // If connection not open, abort and return error status.
        if (! $this->ping()) {
            return ['status' => StatusServiceProvider::STATUS_ERROR];
        }

        $status = ['status' => StatusServiceProvider::STATUS_OK];

        // If no info command params to execute, just return ok status.
        if (count($this->infoCommands) === 0) {
            return $status;
        }

        $details = [];

        // Get all info command params data and merge into a single array.
        foreach ($this->infoCommands as $command) {
            // Flatten the array that comes back from the various info commands (Predis puts details in subkeys whereas PhpRedis does not).
            $details = array_merge(
                $details,
                iterator_to_array(
                    new RecursiveIteratorIterator(new RecursiveArrayIterator($this->executeInfoCommand($command)))
                )
            );
        }

        // Unset excluded keys.
        foreach ($this->excludedKeys as $keys) {
            unset($details[$keys]);
        }

        if (count($details) > 0) {
            $status[$this->detailKey] = $details;
        }

        return $status;
    }
}
