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

namespace Brand0nGG\Contracts\Services\ApexApi;

/**
 * Apex API service interface. Allows interaction with the Apex API via PHP.
 *
 * @see https://apexlegendsapi.com
 *
 * @author Brandon Clothier <brandon14125@gmail.com>
 */
interface ApexApiService
{
    /**
     * Apex API player stats URL.
     *
     * @var string
     */
    public const APEX_API_PLAYER_STATS_URL = 'https://api.mozambiquehe.re/bridge?version=5';
    /**
     * Apex API map rotation URL.
     *
     * @var string
     */
    public const APEX_API_MAP_ROTATION_URL = 'https://api.mozambiquehe.re/maprotation?version=2';
    /**
     * Apex API new URL.
     *
     * @var string
     */
    public const APEX_API_NEWS_URL = 'https://api.mozambiquehe.re/news';
    /**
     * Apex API server status URL.
     *
     * @var string
     */
    public const APEX_API_SERVER_STATUS_URL = 'https://api.mozambiquehe.re/servers';
    /**
     * Apex API origin data URL.
     *
     * @var string
     */
    public const APEX_API_ORIGIN_URL = 'https://api.mozambiquehe.re/origin';
    /**
     * Apex API name to uid URL.
     *
     * @var string
     */
    public const APEX_API_NAME_TO_UID_URL = 'https://api.mozambiquehe.re/nametouid';

    /**
     * Valid list of Apex Legends platforms available on the Apex API.
     *
     * @var array
     */
    public const VALID_PLATFORMS = ['PC', 'PS4', 'X1'];

    /**
     * Get player stats via their origin {@link $uid} on a given {@link $platform}.
     *
     * @param int    $uid
     * @param string $platform
     *
     * @return array
     */
    public function getPlayerStats(int $uid, string $platform = 'PC'): array;

    /**
     * Get the player stats by their {@link $userName} on a given {@link $platform}.
     *
     * @param string $userName
     * @param string $platform
     *
     * @return array
     */
    public function getPlayerStatsByName(string $userName, string $platform = 'PC'): array;

    /**
     * Get the current map rotation data.
     *
     * @return array
     */
    public function getMapRotation(): array;

    /**
     * Get Apex Legends news in the desired {@link $lang}
     *
     * @param string $lang
     *
     * @return array
     */
    public function getNews(string $lang = 'en_us'): array;

    /**
     * Get Apex server statuses.
     *
     * @return array
     */
    public function getServerStatus(): array;

    /**
     * Get origin player data from a player {@link userName}.
     *
     * @param string $userName Username
     *
     * @return array
     */
    public function getOriginPlayer(string $userName): array;

    /**
     * Gets user UID from a {@link $userName} for a given {@link $platform}.
     *
     * @param string $userName Username
     * @param string $platform Platform type
     *
     * @return array
     */
    public function nameToUid(string $userName, string $platform = 'PC'): array;
}
