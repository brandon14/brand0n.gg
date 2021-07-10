<?php

declare(strict_types=1);

namespace Brand0nGG\Contracts\Services\Status;

/**
 * Interface PdoQuery
 *
 * Wrapper class to neatly wrap a PDO query string and parameters in a single class.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface PdoQuery
{
    /**
     * Returns the query string formatted in a way that {@link \PDO::prepare()} can understand.
     *
     * @see https://www.php.net/manual/en/pdo.prepare.php
     *
     * @return string
     */
    public function getQuery(): string;

    /**
     * Returns either null if no query params are needed, or an associative array of PDO compatible
     * query params as understood by the {@link \PDOStatement::execute()} method.
     *
     * @see https://www.php.net/manual/en/pdostatement.execute.php
     *
     * @return array|null
     */
    public function getParams(): ?array;
}
