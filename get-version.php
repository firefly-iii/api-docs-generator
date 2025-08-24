<?php

declare(strict_types=1);

use ApiDocBuilder\Cache\Cache;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

$isDevRun = true;
/*
 * Include necessary files:
 */
include 'vendor/autoload.php';
include 'configuration.php';

if ($isDevRun) {
    echo 'main';
    exit(0);
}

/*
 * Create a log channel.
 */
$log       = new Logger('api-docs-generator');
$formatter = new LineFormatter("[%datetime%] %level_name%: %message% %context% %extra%\n", 'Y-m-d H:i:s', true, true);
$handler   = new StreamHandler('php://stdout', Level::Warning);
$handler->setFormatter($formatter);
$log->pushHandler($handler);

$isCached        = Cache::isCached('version.txt');
$softwareVersion = 'main';
if ($isCached) {
    $softwareVersion = Cache::getCached('version.txt');
}
if (!$isCached) {
    $softwareVersion = Cache::getLatestVersion();
    Cache::storeCache('version.txt', $softwareVersion);
}
echo $softwareVersion['last_release_name'];