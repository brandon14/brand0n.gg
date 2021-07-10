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

namespace App\Services\Status\Providers\Pdo;

use Brand0nGG\Contracts\Services\Status\PdoQuery;

/**
 * Class PostgresPdoDetailsQuery
 *
 * {@link \Brand0nGG\Contracts\Services\Status\PdoQuery} to get the database details from a Postgres database.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class PostgresPdoDetailsQuery implements PdoQuery
{
    /**
     * Name of the database.
     *
     * @var string $databaseName
     */
    protected string $databaseName;

    /**
     * Constructs a new PostgresPdoDetailsQuery.
     *
     * @param string $databaseName Database name
     *
     * @return void
     */
    public function __construct(string $databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return 'SELECT date_trunc(\'second\', current_timestamp - pg_postmaster_start_time()) AS uptime,
                       version() AS version,
                       pg_size_pretty(pg_database_size(:database_name)) AS database_size';
    }

    /**
     * {@inheritdoc}
     */
    public function getParams(): ?array
    {
        return [':database_name' => $this->databaseName];
    }
}
