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

namespace App\ViewModels;

use function count;
use function is_array;
use function random_int;
use function array_filter;
use Statamic\View\ViewModel;

/**
 * Class RandomQuote
 *
 * View model class to get a random quote index to display in the landing section.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class RandomQuote extends ViewModel
{
    public function data(): array
    {
        // Get page builder data.
        $pageBuilderData = $this->cascade->get('page_builder')->value();

        if (! is_array($pageBuilderData) || count($pageBuilderData) === 0) {
            return [];
        }

        // Filter out all page builder entries except for the landing section.
        $landingSection = array_filter($pageBuilderData, static function ($data) {
            return ($data['type'] ?? null) === 'landing_section';
        });

        if (count($landingSection) === 0) {
            return [];
        }

        $landingSection = $landingSection[0];

        // Get list of quotes.
        $quotes = $landingSection['landing_section_quotes']->value() ?? [];

        if (! is_array($quotes) || count($quotes) === 0) {
            return [];
        }

        // Get ranodm index for quotes.
        $randomQuoteIndex = random_int(0, count($quotes) - 1);

        return [
            'random_quote_index' => $randomQuoteIndex,
        ];
    }
}
