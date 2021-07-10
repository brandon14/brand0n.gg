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

use PDO;
use Throwable;
use RuntimeException;
use Brand0nGG\Contracts\Services\Status\PdoQuery;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class PdoProvider.
 *
 * PDO status provider. Allows pinging a database connection created through
 * {@link \PDO} to test the connection.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class PdoProvider implements StatusServiceProvider
{
    /**
     * PDO connection instance. We use PDO here to decouple this provider from any 3rd party
     * library.
     *
     * @var \PDO
     */
    protected PDO $pdo;

    /**
     * Query to run to ping the database.
     *
     * @var \Brand0nGG\Contracts\Services\Status\PdoQuery
     */
    protected PdoQuery $pingQuery;

    /**
     * Query to run to gather details about the database.
     *
     * @var \Brand0nGG\Contracts\Services\Status\PdoQuery|null
     */
    protected ?PdoQuery $detailsQuery;

    /**
     * Detail array key name for database details.
     *
     * @var string
     */
    protected string $detailKey;

    /**
     * Constructs a new database status service provider.
     *
     * @param \PDO                                               $pdo          PDO instance
     * @param \Brand0nGG\Contracts\Services\Status\PdoQuery      $pingQuery    Query to ping database connection
     * @param \Brand0nGG\Contracts\Services\Status\PdoQuery|null $detailsQuery Optional detail query to get more information
     *                                                                         about database
     * @param string                                             $detailKey    Name of the array key for database details
     *
     * @return void
     */
    public function __construct(PDO $pdo, PdoQuery $pingQuery, ?PdoQuery $detailsQuery = null, string $detailKey = 'details')
    {
        $this->setPdo($pdo);
        $this->setPingQuery($pingQuery);
        $this->setDetailsQuery($detailsQuery);
        $this->setDetailKey($detailKey);
    }

    /**
     * Will execute the PDO query and return array of results.
     *
     * @param \Brand0nGG\Contracts\Services\Status\PdoQuery $query     Pdo query data
     * @param int                                           $fetchMode PDO fetch mode
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    protected function executePdo(PdoQuery $query, int $fetchMode = PDO::FETCH_ASSOC): array
    {
        // Execute a query to test the database connection is alive.
        $stmt = $this->pdo->prepare($query->getQuery());

        // Could not create prepared statement.
        if ($stmt === false) {
            throw new RuntimeException('Unable to prepare PDO statement.');
        }

        // Execute query.
        $exec = $stmt->execute($query->getParams());

        // If the execute failed or returned an error code (this will not happen when
        // PDO is configured to throw exceptions, it will just throw which works as well)
        // then return an error status.
        if ($exec === false || $stmt->errorCode() !== '00000') {
            throw new RuntimeException('PDO ping statement execution failed.');
        }

        // Fetch the result (result is an array because of the fetch_type of FETCH_NUM).
        return $stmt->fetchAll($fetchMode);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): array
    {
        try {
            $pingResult = $this->executePdo($this->pingQuery);

            if (count($pingResult) <= 0) {
                throw new RuntimeException('Unable to retrieve query results.');
            }

            $status = ['status' => StatusServiceProvider::STATUS_OK];

            // Execute optional details query if present.
            if ($this->detailsQuery !== null) {
                $details = $this->executePdo($this->detailsQuery);

                $status[$this->detailKey] = $details;
            }

            return $status;
        } catch (Throwable $t) {
            // Swallow exceptions on purpose.
        }

        return ['status' => StatusServiceProvider::STATUS_ERROR];
    }

    /**
     * Get PDO instance.
     *
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Set PDO instance.
     *
     * @param \PDO $pdo PDO instance
     *
     * @return \Brand0nGG\Services\Status\Providers\PdoProvider
     */
    public function setPdo(PDO $pdo): self
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Get ping query.
     *
     * @return \Brand0nGG\Contracts\Services\Status\PdoQuery
     */
    public function getPingQuery(): PdoQuery
    {
        return $this->pingQuery;
    }

    /**
     * Set ping query.
     *
     * @param \Brand0nGG\Contracts\Services\Status\PdoQuery $pingQuery
     *
     * @return \Brand0nGG\Services\Status\Providers\PdoProvider
     */
    public function setPingQuery(PdoQuery $pingQuery): self
    {
        $this->pingQuery = $pingQuery;

        return $this;
    }

    /**
     * Get details query.
     *
     * @return \Brand0nGG\Contracts\Services\Status\PdoQuery|null
     */
    public function getDetailsQuery(): ?PdoQuery
    {
        return $this->detailsQuery;
    }

    /**
     * Set details query.
     *
     * @param \Brand0nGG\Contracts\Services\Status\PdoQuery|null $detailsQuery
     *
     * @return \Brand0nGG\Services\Status\Providers\PdoProvider
     */
    public function setDetailsQuery(?PdoQuery $detailsQuery = null): self
    {
        $this->detailsQuery = $detailsQuery;

        return $this;
    }

    /**
     * Get database details array key.
     *
     * @return string
     */
    public function getDetailKey(): string
    {
        return $this->detailKey;
    }

    /**
     * Set database details array key.
     *
     * @param string $detailKey
     *
     * @return \Brand0nGG\Services\Status\Providers\PdoProvider
     */
    public function setDetailKey(string $detailKey = 'details'): self
    {
        $this->detailKey = $detailKey;

        return $this;
    }
}
