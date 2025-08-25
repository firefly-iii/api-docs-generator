<?php
/*
 * Cache.php
 * Copyright (c) 2025 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace ApiDocBuilder\Cache;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class Cache
 */
class Cache
{
    public static function isCached(string $file): bool
    {
        global $log; // lame, I know.
        $cacheDir = sprintf('%s/cache', ROOT);
        $file     = sprintf('%s/%s', $cacheDir, $file);
        if (file_exists($file)) {
            if (time() - filemtime($file) < 86400) { // one day
                $log->debug(sprintf('Cache file "%s" is still valid.', $file));
                return true;
            }
        }
        $log->debug(sprintf('Cache file "%s" is not valid.', $file));
        return false;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public static function getLatestVersion(): array
    {
        global $log;
        $url = 'https://api.github.com/repos/firefly-iii/firefly-iii/releases';

        // information:
        $lastDate    = Carbon::create(2000, 1, 1);
        $lastVersion = '0.0.1';
        $client      = new Client();
        $log->debug(sprintf('Fetching version data from "%s"', $url));
        try {
            $res = $client->get($url, [
                'headers' => [
                    'Accept'     => 'application/vnd.github+json',
                    'User-Agent' => 'Firefly III API doc generator script/1.0',
                ],
            ]);
        } catch (GuzzleException $e) {
            die(sprintf('Could not fetch data from GitHub: %s', $e->getMessage()));
        }
        $body = (string) $res->getBody();
        $json = json_decode($body, true);

        /** @var array $item */
        foreach ($json as $item) {
            $version = $item['name'];

            if (str_starts_with($version, 'Development release')) {
                continue;
            }

            // replace some obvious prefixes:
            if (str_starts_with($version, 'v')) {
                $version = substr($version, 1);
            }
            // firefly-iii-stack-
            if (str_starts_with($version, 'firefly-iii-stack-')) {
                $version = substr($version, 18);
            }
            // importer-
            if (str_starts_with($version, 'importer-')) {
                $version = substr($version, 9);
            }
            // firefly-iii-
            if (str_starts_with($version, 'firefly-iii-')) {
                $version = substr($version, 12);
            }

            $result = version_compare($lastVersion, $version);
            if (-1 === $result) {
                // this one is newer!
                $lastVersion = $version;
                $lastDate    = Carbon::parse($item['created_at'])->format('j F Y');
            }
        }
        sleep(2);
        if ('0.0.1' === $lastVersion) {
            die('Could not find last release.');
        }
        $log->debug(sprintf('Found version "%s"', $lastVersion));
        return [
            'last_release_date' => $lastDate,
            'last_release_name' => $lastVersion,
        ];
    }

    public static function storeCache(string $file, array $version): void
    {
        $cacheDir = sprintf('%s/cache', ROOT);
        $file     = sprintf('%s/%s', $cacheDir, $file);
        file_put_contents($file, json_encode($version));
    }

    public static function getCached(string $file): array
    {
        $cacheDir = sprintf('%s/cache', ROOT);
        $file     = sprintf('%s/%s', $cacheDir, $file);
        return json_decode(file_get_contents($file), true);
    }

}
