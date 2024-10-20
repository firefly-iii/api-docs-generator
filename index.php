<?php

declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

// default values:
$version     = '0.1-beta';
$server      = 'https://demo.firefly-iii.org';
$destination = './';
$tags        = [];
$directories = [];
$apiVersions = ['v2'];

include 'vendor/autoload.php';
include 'config.php';

// create a log channel
$log       = new Logger('api-docs-generator');
$formatter = new LineFormatter("[%datetime%] %level_name%: %message% %context% %extra%\n", 'Y-m-d H:i:s', true, true);
$handler   = new StreamHandler('php://stdout', Level::Debug);
$handler->setFormatter($formatter);
$log->pushHandler($handler);

$templatesDir = sprintf('%s/templates', ROOT);
$cacheDir     = sprintf('%s/cache', ROOT);

$builder = new Builder($templatesDir, $cacheDir);
$builder->setLogger($log);
$builder->setVersion($version);
$builder->setApiVersions($apiVersions);
$builder->setServer($server);

$log->debug('Start building API docs');

// add tags
/**
 * @var string $name
 * @var array $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info);
}
unset($name);

// two sets of schema's:
// v1 and v2.

/** @var array $info */
foreach ($directories as $info) {
    /** @var string $apiVersion */
    foreach ($info['api_version'] as $apiVersion) {
        $log->debug(sprintf('Add directory "%s" to version "%s"', $info['path'], $apiVersion));
        // list all files in the directory:
        $fullDirectory = sprintf('%s/%s', ROOT, $info['path']);
        $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory), RecursiveIteratorIterator::SELF_FIRST);

        // sort all files in the directory:
        $list = [];
        /**
         * @var string $fullPath
         * @var SplFileInfo $object
         */
        foreach ($objects as $fullPath => $object) {
            if (str_ends_with($fullPath, 'yaml') && !str_contains($fullPath, 'ignore')) {
                $list[$fullPath] = $object;
            }

        }
        ksort($list);

        /**
         * @var string $fullPath
         * @var SplFileInfo $object
         */
        foreach ($list as $fullPath => $object) {
            // add to thing:
            $log->debug(sprintf('Add "%s" file "%s"', $info['identifier'], $fullPath));
            $builder->addYamlFile($apiVersion, $info['identifier'], $fullPath, $info['indentation']);
        }
    }
}
foreach ($apiVersions as $apiVersion) {
    $result           = $builder->render($apiVersion);
    $finalDestination = sprintf('%s/firefly-iii-%s-%s.yaml', $destination, $version, $apiVersion);
    file_put_contents($finalDestination, $result);
}


