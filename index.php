<?php
declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;

// default values:
$version     = '0.1-beta';
$pathVersion = 'v1';
$server      = 'https://demo.firefly-iii.org';
$destination = './';
$tags        = [];

include 'vendor/autoload.php';
include 'config.php';

$templatesDir = sprintf('%s/templates', ROOT);
$cacheDir     = sprintf('%s/cache', ROOT);

$builder = new Builder($templatesDir, $cacheDir);
$builder->setVersion($version);
$builder->setPathVersion($pathVersion);
$builder->setServer($server);

// add tags
/**
 * @var string $name
 * @var array $info
 */
foreach ($tags[$pathVersion] as $name => $info) {
    $builder->addTag($name, $info['description']);
}
unset($name);

// scan directories and add all paths:
$directories = [
    [
        'path'        => sprintf('yaml/%s/paths', $pathVersion),
        'identifier'  => 'paths',
        'indentation' => 1,
    ],
    [
        'path'        => sprintf('yaml/%s/schemas', $pathVersion),
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
    [
        'path'        => 'yaml/shared/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],
];

foreach ($directories as $info) {

    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $info['path']);
    $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory), RecursiveIteratorIterator::SELF_FIRST);
    /**
     * @var string $fullPath
     * @var SplFileInfo $object
     */
    foreach ($objects as $fullPath => $object) {
        // add to thing:
        if (str_ends_with($fullPath, 'yaml')) {
            $builder->addYamlFile($info['identifier'], $fullPath, $info['indentation']);
        }
    }
}

$result           = $builder->render();
$finalDestination = sprintf('%s/firefly-iii-%s-%s.yaml', $destination, $pathVersion, $version);

file_put_contents($finalDestination, $result);

