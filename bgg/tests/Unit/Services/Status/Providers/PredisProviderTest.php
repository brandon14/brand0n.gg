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

use Predis\ClientInterface;
use Predis\PredisException;
use PHPUnit\Framework\TestCase;
use Brand0nGG\Services\Status\Providers\PredisProvider;
use Brand0nGG\Contracts\Services\Status\StatusServiceProvider;

/**
 * Class PredisProviderTest.
 *
 * PredisProvider tests.
 *
 * In this test, we want to be able to test the functionality of our class that relies on
 * an {@link \Predis\ClientInterface}, but we don't actually want to require a redis server
 * set up just to unit test. So we pass that interface into the class, and that allows us to
 * mock it away during testing. As long as our mock behaves as a {@link \Predis\ClientInteraface}
 * would, then we can be confident our class performs like we expect it to.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class PredisProviderTest extends TestCase
{
    /**
     * Test that the provider will handle when Predis client throws an exception.
     */
    public function test_provider_handles_exception_thrown_from_predis(): void
    {
        $mock = $this->createMock(ClientInterface::class);;

        // Tell mocked Predis client to throw an exception when we hit the ping method.
        $mock->expects($this::once())->method('ping')->will(
            $this::throwException(new MockPredisException('This is a test.'))
        );

        $instance = new PredisProvider($mock);

        $status = $instance->getStatus();

        // Should return an error status array.
        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that provider will return an error status if it gets not PONG from redis.
     */
    public function test_providers_returns_error_status_if_ping_not_successful(): void
    {
        $mock = $this->createMock(ClientInterface::class);

        // Tell mocked Predis client to return a string other than PONG.
        $mock->expects($this::once())->method('ping')->willReturn('NOT PONG');

        $instance = new PredisProvider($mock);

        $status = $instance->getStatus();

        // Should return an error status array.
        $this::assertEquals(['status' => StatusServiceProvider::STATUS_ERROR], $status);
    }

    /**
     * Test that provider will return an OK status when it gets a PONG back from redis.
     */
    public function test_provider_returns_ok_status_on_successful_pong(): void
    {
        $mock = $this->createMock(ClientInterface::class);

        // Tell mocked Predis client to return PONG.
        $mock->expects($this::once())->method('ping')->willReturn('PONG');

        $instance = new PredisProvider($mock);

        $status = $instance->getStatus();

        // Should return an OK status array.
        $this::assertEquals(['status' => StatusServiceProvider::STATUS_OK], $status);
    }
}

/**
 * Class MockPredisException.
 *
 * A mock {@link \Predis\PredisException} to force the client to throw during testing.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class MockPredisException extends PredisException
{
    // intentionally left blank.
}
