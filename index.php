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
//echo '<pre>';

// add tags
/**
 * @var string $name
 * @var array $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info['description']);
}
unset($name);

// scan directories and add all paths:
$directories = [
    [
        'path'        => 'yaml/v1/paths',
        'identifier'  => 'paths',
        'indentation' => 1,
    ],
    [
        'path'        => 'yaml/v2/paths',
        'identifier'  => 'paths',
        'indentation' => 1,
    ],

    [
        'path'        => 'yaml/v1/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],

    [
        'path'        => 'yaml/v2/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
    ],


];

/** @var array $info */
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
        if ('yaml' === substr($fullPath, -4)) {
            //echo sprintf("Added %s\n", $fullPath);

            //echo sprintf("Adding file %s\n", $fullPath);
            $builder->addYamlFile($info['identifier'], $fullPath, $info['indentation']);
        }
    }
}

$result = $builder->render();
//echo $result;
// put in file:
// put result in file:
$finalDestination = sprintf('%s/firefly-iii-%s.yaml', $destination, $version);

file_put_contents($finalDestination, $result);

exit;






