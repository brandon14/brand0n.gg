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

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use function random_int;
use Romans\Filter\IntToRoman;
use Statamic\Contracts\Globals\Variables;
use Statamic\Contracts\Globals\GlobalRepository;
use Brand0nGG\Contracts\Services\ApexApi\ApexApiService;

/**
 * Class FossabotController
 *
 * Controller class to handle all Fossabot chat command requests.
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
final class FossabotController extends Controller
{
    /**
     * Statamic globals variables.
     *
     * @var \Statamic\Contracts\Globals\Variables
     */
    private Variables $globals;

    /**
     * Constructs a new FossabotController.
     *
     * @param \Statamic\Contracts\Globals\GlobalRepository $globals Statamic globals repository
     *
     * @return void
     */
    public function __construct(GlobalRepository $globals)
    {
        $this->globals = $globals->findByHandle('fossabot')->inDefaultSite();
    }

    /**
     * Returns the configured generic command exception message.
     *
     * @return string
     */
    private function returnExceptionMessage(): string
    {
        $message = $this->globals->get('exception_response') ?? 'Oh snap, we had some trouble with your request';
        $emoji = $this->getRandomEmoji($this->globals->get('exception_emojis') ?? []) ?? 'NotLikeThis';

        return "{$message} {$emoji}";
    }

    /**
     * Gets {@link \Carbon\Carbon} instance of birthday defined in the config.
     *
     * @throws \Carbon\Exceptions\InvalidFormatException
     *
     * @return \Carbon\Carbon
     */
    private function getBirthday(): Carbon
    {
        $birthday = $this->globals->get('birthday');
        $timezone = $this->globals->get('birthday_timezone');

        return Carbon::parse($birthday)->setTimezone($timezone);
    }

    /**
     * Gets a random emoji for the given array of emojis, or returns null if none is available.
     *
     * @param array|null $emojis Array of emojis
     * @psalm-param string[] $emojis
     *
     * @return string|null
     */
    private function getRandomEmoji(array $emojis = null): ?string
    {
        $index = random_int(0, count($emojis ?? []) - 1);

        return ((string) $emojis[$index]) ?? null;
    }

    /**
     * Calculates the days until next birthday of the birthday defined in the config and
     * returns a Twitch chat message containing the results.
     *
     * @return string
     */
    public function birthday(): string
    {
        try {
            // Get the date at the start of current date.
            $date = Carbon::now();
            $date->hour = 0;
            $date->minute = 0;
            $date->second = 0;

            $birthday = $this->getBirthday();

            // Set the year to current year since we only care about how long until the next
            // birthday is.
            $birthday->year = $date->year;

            // Its the day of my birth! PogChamp!
            if ($birthday->equalTo($date)) {
                $response = $this->globals->get('on_birthday_response') ?? 'It\'s ma birthday';
                $emoji = $this->getRandomEmoji($this->globals->get('on_birthday_emojis') ?? []) ?? 'FeelsBirthdayMan';

                return $emoji !== null ? "{$response} {$emoji}" : $response;
            }

            // Birthday already happened this year, ggs we go agane!
            if ($birthday->lessThan($date)) {
                ++$birthday->year;
            }

            // Get the diff in days until my next birthday.
            $daysUntil = $birthday->diffInDays($date);

            // Build chat message using configured response template and emojis.
            $response = $this->globals->get('birthday_response') ?? 'My birthday is in %daysUntil% days';
            $response = str_replace('%daysUntil%', (string) $daysUntil, $response);
            $emoji = $this->getRandomEmoji($this->globals->get('birthday_emojis') ?? []) ?? 'FeelsBirthdayMan';

            return "{$response} {$emoji}";
        } catch (Throwable $t) {
            return $this->returnExceptionMessage();
        }
    }

    /**
     * Calculates age using defined birthday and returns a Twitch chat message contain the results.
     *
     * @return string
     */
    public function age(): string
    {
        try {
            // Current date.
            $date = Carbon::now();

            // Birthdate.
            $birthday = $this->getBirthday();

            // Get the difference between the two in years.
            $age = $date->diffInYears($birthday);

            // Build chat message using configured response template and emojis.
            $response = $this->globals->get('age_response') ?? '%age% (Birthday 08/11)';
            $response = str_replace('%age%', (string) $age, $response);
            $emoji = $this->getRandomEmoji($this->globals->get('age_emojis') ?? []) ?? 'FeelsBirthdayMan';

            return "{$response} {$emoji}";
        } catch (Throwable $t) {
            return $this->returnExceptionMessage();
        }
    }

    /**
     * Gets Apex rank for player defined in config and returns a
     * Twitch chat message containing the results.
     *
     * @param \Brand0nGG\Contracts\Services\ApexApi\ApexApiService $apex       Apex API service instance
     * @param \Romans\Filter\IntToRoman                            $intToRoman Int to Roman Numeral converter
     *
     * @return string
     */
    public function apexRank(ApexApiService $apex, IntToRoman $intToRoman): string
    {
        try {
            // Get my user account ID.
            $uid = (int) $this->globals->get('apex_account_uid');

            // Fetch account stats from ApexAPI.
            $data = $apex->getPlayerStats($uid);

            // Build chat message using configured response template and emojis.
            $response = $this->globals->get('apex_rank_response') ?? '%rankName% %rankDiv%';
            $response = str_replace(
                [
                    '%rankDiv%',
                    '%rankName%',
                ],
                [
                    $intToRoman->filter((int)($data['global']['rank']['rankDiv'] ?? 4)),
                    $data['global']['rank']['rankName'] ?? 'Bronze',
                ],
                $response
            );
            $emoji = $this->getRandomEmoji($this->globals->get('apex_rank_emojis') ?? []) ?? 'PepegaAim';

            return "{$response} {$emoji}";
        } catch (Throwable $t) {
            return $this->returnExceptionMessage();
        }
    }

    /**
     * Fallback method to display a general command not found chat friendly message.
     *
     * @return string
     */
    public function notFound() : string
    {
        try {
            $response = $this->globals->get('not_found_response') ?? 'Oh snap, bruh that command does not exist';
            $emoji    = $this->getRandomEmoji($this->globals->get('not_found_emojis') ?? []) ?? 'NotLikeThis';

            return "{$response} {$emoji}";
        } catch (Throwable $t) {
            return $this->returnExceptionMessage();
        }
    }
}
