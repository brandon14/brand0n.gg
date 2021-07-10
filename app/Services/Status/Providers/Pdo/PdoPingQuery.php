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
 * Class PdoPingQuery
 *
 * {@link \Brand0nGG\Contracts\Services\Status\PdoQuery} to ping the database.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
class PdoPingQuery implements PdoQuery
{
    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return 'SELECT 1+1 AS result';
    }

    /**
     * {@inheritdoc}
     */
    public function getParams(): ?array
    {
        return null;
    }
}
