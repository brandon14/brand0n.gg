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

namespace Brand0nGG\Contracts\Services;

use Throwable;
use function implode;
use function hash_algos;
use InvalidArgumentException;

/**
 * Class InvalidHashAlgorithmException.
 *
 * Exception thrown when an invalid hash algorithm string is provided
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class InvalidHashAlgorithmException extends InvalidArgumentException
{
    /**
     * Creates a new exception when an invalid hash algorithm is encountered.
     *
     * @param string          $algo     Hash algorithm provided
     * @param int             $code     Error code
     * @param \Throwable|null $previous Previous exception
     *
     * @return \Brand0nGG\Contracts\Services\InvalidHashAlgorithmException
     */
    public static function invalidAlgorithm(string $algo, int $code = 0, ?Throwable $previous = null): self
    {
        $supportedAlgos = implode(', ', hash_algos());

        return new self(
            "Invalid hash algorithm [{$algo}] provided. Please use one of the following supported algorithms: [{$supportedAlgos}]",
            $code,
            $previous
        );
    }
}
