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

// scan directories and add all paths:
$directories = [
    'yaml/paths/autocomplete',
    'yaml/paths/charts',
    'yaml/paths/data',
    'yaml/paths/insight',
    'yaml/paths/summary',
    'yaml/paths/models',
];

foreach ($directories as $directory) {

    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $directory);
    $files         = scandir($fullDirectory, SCANDIR_SORT_ASCENDING);

    // loop al files in this directory:
    foreach ($files as $file) {
        // must be YAML file.
        $fullPath = sprintf('%s/%s', $fullDirectory, $file);

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
    'yaml/schemas/models', // always need this

];

foreach ($directories as $directory) {
    // list all files in the directory:
    $fullDirectory = sprintf('%s/%s', ROOT, $directory);
    $files         = scandir($fullDirectory, SCANDIR_SORT_ASCENDING);

    // loop al files in this directory:
    foreach ($files as $file) {
        // must be YAML file.
        $fullPath = sprintf('%s/%s', $fullDirectory, $file);

        // add to thing:
        if ('yaml' === substr($fullPath, -4)) {
            //echo sprintf("Added %s\n", $fullPath);
            //echo sprintf("Adding file %s\n", $fullPath);
            $builder->addYamlFile('schemas', $fullPath, 2);
        }
    }
}
echo "\n\n";
$result = $builder->render();
echo $result;
// put in file:
// put result in file:
$finalDestination = sprintf('%s/firefly-iii-%s.yaml', $destination, $version);

file_put_contents($finalDestination, $result);

exit;






