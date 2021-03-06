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

namespace Brand0nGG\Tests\Unit\Services\Status\Providers;

use Brand0nGG\Contracts\Services\Status\PdoQuery;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Brand0nGG\Services\Status\Providers\PdoProvider;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class PdoProviderTest.
 *
 * PdoProvider tests.
 *
 * It's easy to imagine testing a class that uses a database connection (in this case an
 * {@link \PDO} connection) would actually connect to a database. But by passing in the
 * {@link \PDO} connection object to the class, we can mock that object in our tests below
 * so we never have to leave the application boundary to test this class. As long as we
 * force our mock to behave as a normal {@link \PDO} class would (think adhering to the
 * documentation for the {@link \PDO} class), then we can be sure our class behaves in accordance
 * given our assumption of how the {@link \PDO} class behaves.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class PdoProviderTest extends TestCase
{
    /**
     * Test that the provider will handle when the PDO instance throws an exception.
     */
    public function test_get_status_handles_pdo_exceptions(): void
    {
        $db = $this->createMock(PDO::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        $db->expects($this::once())->method('query')->will(
            $this::throwException(new PDOException('This is a test PDO exception'))
        );

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test the the provider will handle when the PDO statement fails (returns false).
     */
    public function test_get_status_handles_statement_failure(): void
    {
        $db = $this->createMock(PDO::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect query to be called, and we mock it returning false to simulate a failure to create the
        // PDOStatement object.
        $db->expects($this::once())->method('query')->willReturn(false);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider will handle when the statement execution fails (returns false).
     */
    public function test_get_status_handles_statement_exec_failure(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to return false to simulate the statement execution failure.
        $statement->expects($this::once())->method('execute')->willReturn(false);

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider handles when execute throws a {@link \PDOException}.
     */
    public function test_get_status_handles_statement_exec_pdoexception(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to throw an exception to simulate a query failure when PDO is set
        // to throw exceptions.
        $statement->expects($this::once())->method('execute')->will(
            $this::throwException(new PDOException('This is a test!'))
        );

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider will handle when the query executed returns a error code.
     */
    public function test_get_status_handles_statement_exec_query_state_error(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to return true.
        $statement->expects($this::once())->method('execute')->willReturn(true);
        // Simulate a call to errorCode returning something other than 00000 which is the SQL state
        // for success.
        $statement->expects($this::once())->method('errorCode')->willReturn('01002');

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider handles when fetch throws a {@link \PDOException}.
     */
    public function test_get_status_handles_fetch_pdoexception(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to return true.
        $statement->expects($this::once())->method('execute')->willReturn(true);
        // Mock a success error code.
        $statement->expects($this::once())->method('errorCode')->willReturn('00000');
        // Mock that fetching the results throws a PDOException
        $statement->expects($this::once())->method('fetch')->will(
            $this::throwException(new PDOException('This is a test!'))
        );

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider handles getting empty query results (should not happen unless something weird
     * is going on).
     */
    public function test_get_status_handles_fetch_empty_query_result(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to return true.
        $statement->expects($this::once())->method('execute')->willReturn(true);
        // Mock a success error code.
        $statement->expects($this::once())->method('errorCode')->willReturn('00000');
        // Mock the query returning no results. I mean this should never happen right? SELECT 1+1 never returning
        // 2? Oh well sue me.
        $statement->expects($this::once())->method('fetch')->willReturn([]);

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that the provider returns the database provider returns a status of OK if everything
     * checks out.
     */
    public function test_get_status_returns_database_status(): void
    {
        $db = $this->createMock(PDO::class);
        $statement = $this->createMock(PDOStatement::class);
        $pingQuery = $this->createMock(PdoQuery::class);

        $pingQuery->expects($this::once())->method('getQuery')->willReturn(
            'SELECT 1+1 AS result'
        );

        $pingQuery->expects($this::once())->method('getParams')->willReturn(null);

        // We expect that the execute function will be called on the statement mock and
        // we mock it to return true.
        $statement->expects($this::once())->method('execute')->willReturn(true);
        // Mock a success error code.
        $statement->expects($this::once())->method('errorCode')->willReturn('00000');
        // Mock the query returning the correct results meaning we could hit the database with this
        // connection.
        $statement->expects($this::once())->method('fetch')->willReturn([0 => 2]);

        // We expect query to be called, and we mock it returning the mocked statement.
        $db->expects($this::once())->method('query')->willReturn($statement);

        $instance = new PdoProvider($db, $pingQuery);

        $status = $instance->getStatus();

        $this::assertEquals(['status' => StatusServiceProvider::STATUS_OK], $status);
    }
}
