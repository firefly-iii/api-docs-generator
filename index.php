<?php
declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

// default values:
$version     = '0.1-beta';
$pathVersion = 'v1';
$server      = 'https://demo.firefly-iii.org';
$destination = './';
$tags        = [];

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
$builder->setPathVersion($pathVersion);
$builder->setServer($server);

$log->debug('Start building API docs');

// add tags
/**
 * @var string $name
 * @var array  $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info['description']);
    $log->debug(sprintf('Add tag "%s"', $name));
}
unset($name);

// scan directories and add all paths:
$directories = [
    [
        'path'         => 'yaml/v1/paths',
        'identifier'   => 'paths',
        'indentation'  => 1,
        'path_version' => 'v1',
    ],
    [
        'path'         => 'yaml/v1/schemas',
        'identifier'   => 'schemas',
        'indentation'  => 2,
        'path_version' => 'v1',
    ],
    [
        'path'         => 'yaml/v2/paths',
        'identifier'   => 'paths',
        'indentation'  => 1,
        'path_version' => 'v2',
    ],
    [
        'path'         => 'yaml/v2/schemas',
        'identifier'   => 'schemas',
        'indentation'  => 2,
        'path_version' => 'v2',
    ],
    [
        'path'        => 'yaml/shared/models',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
    [
        'path'        => 'yaml/shared/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
    [
        'path'        => 'yaml/shared/properties',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
    [
        'path'        => 'yaml/shared/filters',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
];

foreach ($directories as $info) {
    $log->debug(sprintf('Add directory "%s"', $info['path']));

    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $info['path']);
    $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory), RecursiveIteratorIterator::SELF_FIRST);
    /**
     * @var string      $fullPath
     * @var SplFileInfo $object
     */
    foreach ($objects as $fullPath => $object) {
        // add to thing:
        if (str_ends_with($fullPath, 'yaml')) {
            $log->debug(sprintf('Add "%s" file "%s"', $info['identifier'], $fullPath));
            $builder->addYamlFile($info['identifier'], $fullPath, $info['indentation'], $info['path_version'] ?? null);
        }
    }
}

$result           = $builder->render();
$finalDestination = sprintf('%s/firefly-iii-%s.yaml', $destination, $version);

file_put_contents($finalDestination, $result);

