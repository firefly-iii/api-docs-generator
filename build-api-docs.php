<?php

declare(strict_types=1);

use ApiDocBuilder\Builder\Builder;
use ApiDocBuilder\Cache\Cache;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

// default values:
$version         = '0.1-beta';
$server          = 'https://demo.firefly-iii.org';
$destination     = './';
$tags            = [];
$directories     = [];
$softwareVersion = $argv[1] ?? 'develop';
if('main' === $softwareVersion) {
    $softwareVersion = 'develop';
}

/*
 * Include necessary files:
 */
include 'vendor/autoload.php';
include 'configuration.php';

/*
 * Create a log channel.
 */
$log       = new Logger('api-docs-generator');
$formatter = new LineFormatter("[%datetime%] %level_name%: %message% %context% %extra%\n", 'Y-m-d H:i:s', true, true);
$handler   = new StreamHandler('php://stdout', Level::Debug);
$handler->setFormatter($formatter);
$log->pushHandler($handler);

/*
 * Create the builder.
 */
$builder = new Builder(sprintf('%s/templates', ROOT), sprintf('%s/cache', ROOT));
$builder->setLogger($log);
$builder->setVersion($softwareVersion);
$builder->setServer($server);

$log->debug(sprintf('Start building API docs for version %s', $softwareVersion));

/*
 * Add tags to builder.
 */
/**
 * @var string $name
 * @var array $info
 */
foreach ($tags as $name => $info) {
    $builder->addTag($name, $info);
}
unset($name, $info);

/*
 * Add directories to builder.
 */
/** @var array $directory */
foreach ($directories as $directory) {
    $fullDirectory = realpath(sprintf('%s/%s', ROOT, $directory['path']));
    if (false === $fullDirectory) {
        $log->error(sprintf('Could not find directory "%s", exit.', sprintf('%s/%s', ROOT, $directory['path'])));
        exit(1);
    }
    $log->debug(sprintf('Add directory "%s".', $fullDirectory));

    // check each file in the directory and see if it needs action.
    // collect recursively:
    $list      = [];
    $it        = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullDirectory, \RecursiveDirectoryIterator::SKIP_DOTS));
    $Regex     = new \RegexIterator($it, '/^.+\.yaml$/i', \RecursiveRegexIterator::GET_MATCH);
    $fullPaths = [];
    foreach ($Regex as $item) {
        $list[] = $item[0];
    }

    sort($list);
    $log->debug(sprintf('Found %d file(s) in directory "%s"', count($list), $fullDirectory));
    /**
     * @var string $fullPath
     */
    foreach ($list as $fullPath) {
        // add to thing:
        $log->debug(sprintf('Add "%s" file "%s"', $directory['identifier'], $fullPath));
        $builder->addYamlFile($directory['identifier'], $fullPath, $directory['indentation']);
    }
}

/*
 * Render the API docs and store the file.
 */
// TODO hard coded references to v1, remove these.
$result           = $builder->render();
$finalDestination = sprintf('%s/firefly-iii-%s-v1.yaml', $destination, $softwareVersion);
$log->debug(sprintf('Write YAML file to %s', $finalDestination));
file_put_contents($finalDestination, $result);

/*
 * Render the API docs index.html file again.
 */
$templateFile = sprintf('%s/index.html.template', ROOT);
if (!file_exists($templateFile)) {
    $log->error(sprintf('Could not find template file "%s", will not update.', $templateFile));
    exit;
}
$templateContent = file_get_contents($templateFile);
$urls            = [];
$developUrls     = [];


// list all YAML files in the destination directory (dist).
// check each file in the directory and see if it needs action.
// collect recursively:
$it        = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($destination, \RecursiveDirectoryIterator::SKIP_DOTS));
$Regex     = new \RegexIterator($it, '/^.+\.yaml$/i', \RecursiveRegexIterator::GET_MATCH);
$fullPaths = [];
foreach ($Regex as $item) {
    $path                       = $item[0];
    $fullPaths[basename($path)] = $path;
}

foreach ($fullPaths as $fileName => $fullName) {
    // get exact version (with "-v1" or "-v2")
    $exactVersion = str_replace(['.yaml', 'firefly-iii-'], '', $fileName);
    $compare      = $exactVersion;
    // in version, replace -v1 and -v2 with something version_compare can handle.
    if (str_contains($exactVersion, 'beta')) {
        $compare = str_replace('-v1', '', $exactVersion);
        $compare = str_replace('-v2', '', $compare);
    }
    if (!str_contains($exactVersion, 'beta')) {
        $compare = str_replace('-v1', '-beta.100', $exactVersion);
        $compare = str_replace('-v2', '-alpha.100', $compare);
    }

    // get nice name of version
    $versionName = str_replace('-v1', ' (v1)', $exactVersion);
    $versionName = str_replace('-v2', ' (v2)', $versionName);
    if (str_contains($exactVersion, 'develop')) {
        $developUrls[] = ['url' => sprintf('./%s', $fileName), 'name' => $versionName, 'version' => $compare];
        continue;
    }
    $log->info(sprintf('Added compare version %s', $compare));

    $urls[] = ['url' => sprintf('./%s', $fileName), 'name' => $versionName, 'version' => $compare];
}
uasort($urls, function ($a, $b) {
    return version_compare($b['version'], $a['version']);
});

array_splice($urls, 2, 0, $developUrls);

// remove 'version' key from each array element
foreach ($urls as &$url) {
    unset($url['version']);
}

$json = json_encode($urls, JSON_PRETTY_PRINT);
$templateContent = str_replace('%%URLS%%', $json, $templateContent);
file_put_contents(sprintf('%s/index.html', $destination), $templateContent);
$log->info(sprintf('Destination path is "%s"', $destination));
$log->info(sprintf('Wrote new html file to "%s"', sprintf('%s/index.html', $destination)));
$log->info('Done!');