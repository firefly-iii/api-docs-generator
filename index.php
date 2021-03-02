<?php
declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;

// default values:
$version     = '0.1-beta';
$destination = '/';
$tags        = [];

include 'vendor/autoload.php';
include 'config.php';

$builder = new Builder(sprintf('%s/templates', ROOT), sprintf('%s/cache', ROOT));
$builder->setVersion($version);

// add tags
/**
 * @var string $name
 * @var array  $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info['description']);
}

// scan directories and add them!
$directories = [
    'yaml/autocomplete',
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
            //echo sprintf("Adding file %s\n", $fullPath);
            $builder->addYamlFile('paths', $fullPath, 1);
        }
    }
}

// add all models to the final YAML file by also looping the directory:
$fullDirectory = sprintf('%s/yaml/schemas', ROOT);
$files         = scandir($fullDirectory, SCANDIR_SORT_ASCENDING);
foreach ($files as $file) {
    $fullPath = sprintf('%s/%s', $fullDirectory, $file);
    if ('yaml' === substr($fullPath, -4)) {
        //echo sprintf("Add %s\n", $file);
        $builder->addYamlFile('schemas', ROOT . '/yaml/schemas/' . $file, 2);
    }
}


//$files = scandir(ROOT . '/yaml/paths', SCANDIR_SORT_ASCENDING);

$result = $builder->render();
echo '<pre>';
echo $result;
// put in file:
// put result in file:
$finalDestination = sprintf('%s/firefly-iii-%s.yaml', $destination, $version);

file_put_contents($finalDestination, $result);

exit;






