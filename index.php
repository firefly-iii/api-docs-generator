<?php
declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;

// default values:
$version     = '0.1-beta';
$server      = 'https://demo.firefly-iii.org';
$destination = '/';
$tags        = [];

include 'vendor/autoload.php';
include 'config.php';

$builder = new Builder(sprintf('%s/templates', ROOT), sprintf('%s/cache', ROOT));
$builder->setVersion($version);
$builder->setServer($server);
echo '<pre>';

// add tags
/**
 * @var string $name
 * @var array  $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info['description']);
}
unset($name);

// scan directories and add all paths:
$directories = [
    'yaml/paths/autocomplete',
    'yaml/paths/charts',
    'yaml/paths/data',
    'yaml/paths/insight',
    'yaml/paths/summary',
    'yaml/paths/models',
    'yaml/paths/search',
    'yaml/paths/system',
    'yaml/paths/user',
];

foreach ($directories as $directory) {

    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $directory);
    $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory), RecursiveIteratorIterator::SELF_FIRST);
    /**
     * @var string      $fullPath
     * @var SplFileInfo $object
     */
    foreach ($objects as $fullPath => $object) {
        // add to thing:
        if ('yaml' === substr($fullPath, -4)) {
            //echo sprintf("Added %s\n", $fullPath);

            //echo sprintf("Adding file %s\n", $fullPath);
            $builder->addYamlFile('paths', $fullPath, 1);
        }
    }
}

// scan directories and add all models (schemas):
$directories = [
    'yaml/schemas/arrays', // always need this
    'yaml/schemas/filters', // always need this
    'yaml/schemas/lists', // always need this
    'yaml/schemas/properties', // always need this

    'yaml/schemas/autocomplete',
    'yaml/schemas/charts',
    'yaml/schemas/data',
    'yaml/schemas/insight',
    'yaml/schemas/summary',
    'yaml/schemas/system',
    'yaml/schemas/user',
    'yaml/schemas/models', // always need this

];

foreach ($directories as $directory) {
    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $directory);
    $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory), RecursiveIteratorIterator::SELF_FIRST);

    /**
     * @var string      $fullPath
     * @var SplFileInfo $object
     */
    foreach ($objects as $fullPath => $object) {

        // add to thing:
        if ('yaml' === substr($fullPath, -4)) {
            //echo sprintf("Added %s\n", $fullPath);
            //echo sprintf("Adding file %s\n", $fullPath);
            $builder->addYamlFile('schemas', $fullPath, 2);
        }
    }
}
$result = $builder->render();
echo $result;
// put in file:
// put result in file:
$finalDestination = sprintf('%s/firefly-iii-%s.yaml', $destination, $version);

file_put_contents($finalDestination, $result);

exit;






