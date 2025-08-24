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
$apiVersions     = ['v1'];
$softwareVersion = ['last_release_name' => 'develop'];
$ignoreVersions  = [];

/**
 * @var int $index
 * @var string $argument
 */
foreach ($argv as $index => $argument) {
    if (0 === $index) {
        continue;
    }
    /*
     * Allows you to ignore v1 or v2 (or both) by using --ignore-versions=v1 or --ignore-versions=v1,v2
     */
    if (str_starts_with($argument, '--ignore-versions=')) {
        $ignoreVersions = explode(',', str_replace('--ignore-versions=', '', $argument));
        $ignoreVersions = array_map('trim', $ignoreVersions);
    }
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
 * If this is not a develop run, find out what's the latest version of Firefly III.
 */
if ('false' === getenv('IS_DEVELOP_RUN')) {
    $isCached = Cache::isCached('version.txt');
    if ($isCached) {
        $softwareVersion = Cache::getCached('version.txt');
    }
    if (!$isCached) {
        $softwareVersion = Cache::getLatestVersion();
        Cache::storeCache('version.txt', $softwareVersion);
    }

    if ('true' === getenv('IS_DEVELOP_RUN')) {
        $softwareVersion['last_release_name'] = sprintf('%s-dev', $softwareVersion['last_release_name']);
    }
}


/*
 * Create the builder.
 */
$builder = new Builder(sprintf('%s/templates', ROOT), sprintf('%s/cache', ROOT));
$builder->setLogger($log);
$builder->setVersion($softwareVersion['last_release_name']);
$builder->setApiVersions($apiVersions);
$builder->setServer($server);

$log->debug('Start building API docs');

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
unset($name);


/*
 * Add directories to builder.
 */
/** @var array $info */
foreach ($directories as $info) {
    $log->debug(sprintf('Parse files in directory "%s"', $info['path']));
    /** @var string $apiVersion */
    foreach ($info['api_version'] as $apiVersion) {
        $log->debug(sprintf('Add directory "%s" to version "%s"', $info['path'], $apiVersion));
        // list all files in the directory:
        $fullDirectory = sprintf('%s/%s', ROOT, $info['path']);
        $objects       = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
                                                       RecursiveIteratorIterator::SELF_FIRST);

        // sort all files in the directory:
        $list = [];
        /**
         * @var string $fullPath
         * @var SplFileInfo $object
         */
        foreach ($objects as $fullPath => $object) {
            if (str_ends_with($fullPath, 'yaml') && !str_contains($fullPath, 'ignore')) {
                $list[] = $fullPath;
            }

        }
        sort($list);
        $log->debug(sprintf('Found %d file(s) in directory "%s"', count($list), $info['path']));
        /**
         * @var string $fullPath
         */
        foreach ($list as $fullPath) {
            // add to thing:
            $log->debug(sprintf('Add "%s" file "%s"', $info['identifier'], $fullPath));
            $builder->addYamlFile($apiVersion, $info['identifier'], $fullPath, $info['indentation']);
        }
    }
}

/*
 * Render the API docs and store the file.
 */
foreach ($apiVersions as $apiVersion) {
    if (in_array($apiVersion, $ignoreVersions, true)) {
        $log->warning(sprintf('Will ignore version "%s"', $apiVersion));
        continue;
    }
    $result           = $builder->render($apiVersion);
    $finalDestination = sprintf('%s/dist/firefly-iii-%s-%s.yaml', $destination, $softwareVersion['last_release_name'], $apiVersion);
    file_put_contents($finalDestination, $result);
}

/*
 * Render the API docs index.html file again.
 */
$templateFile = sprintf('%s/src/index.html.template', $destination);
if (!file_exists($templateFile)) {
    $log->error(sprintf('Could not find template file "%s", will not update.', $templateFile));
    exit;
}
$templateContent = file_get_contents($templateFile);
$urls            = [];
$developUrls     = [];
// list all files
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($destination), RecursiveIteratorIterator::SELF_FIRST);
/**
 * @var string $fullPath
 * @var SplFileInfo $object
 */
foreach ($objects as $fullPath => $object) {
    $fileName = $object->getFilename();
    if (str_ends_with($fileName, 'yaml')) {

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
}
uasort($urls, function ($a, $b) {
    return version_compare($b['version'], $a['version']);
});

array_splice($urls, 2, 0, $developUrls);

// remove 'version' key from each array element
foreach ($urls as &$url) {
    echo $url['version'] . PHP_EOL;
    unset($url['version']);
}


$json = json_encode($urls, JSON_PRETTY_PRINT);

$templateContent = str_replace('%%URLS%%', $json, $templateContent);
file_put_contents(sprintf('%s/dist/index.html', $destination), $templateContent);
$log->info(sprintf('Wrote new html file to "%s"', sprintf('%s/index.html', $destination)));
