<?php

declare(strict_types=1);

namespace ApiDocBuilder\Builder;

use Carbon\Carbon;
use Monolog\Logger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Class Builder
 */
class Builder
{
    private array       $paths;
    private array       $schemas;
    private array       $tags;
    private Environment $twig;
    private string      $version;
    private array       $apiVersions;
    private string      $server;
    private Logger      $logger;
    private array       $templates = [];

    /**
     * Builder constructor.
     *
     * @param string $templatePath
     * @param string $cachePath
     */
    public function __construct(string $templatePath, string $cachePath)
    {
        $this->tags      = [];
        $this->paths     = [];
        $this->schemas   = [];
        $this->templates = [];
        $this->server    = '';
        $this->version   = '1.0';

        $loader     = new FilesystemLoader($templatePath);
        $this->twig = new Environment($loader, ['cache' => $cachePath, 'charset' => 'utf-8', 'auto_reload' => true]);
    }

    /**
     * @param string $name
     * @param array $info
     */
    public function addTag(string $name, array $info): void
    {
        foreach ($info['api_version'] as $version) {
            $this->tags[$version]   = $this->tags[$version] ?? [];
            $this->tags[$version][] = [
                'name'        => $name,
                'description' => $info['description'],
            ];
            $this->logger->debug(sprintf('Added tag "%s" to version "%s"', $name, $version));
        }
    }

    /**
     * @param string $apiVersion
     * @param string $identifier
     * @param string $file
     * @param int $indentation
     *
     */
    public function addYamlFile(string $apiVersion, string $identifier, string $file, int $indentation): void
    {
        if (!file_exists($file)) {
            throw new RuntimeException(sprintf('No such file: %s', $file));
        }
        $content = trim((string)file_get_contents($file));
        if ('' === $content) {
            return;
        }
        // parse replacements:
        $replacements  = $this->parseReplacements($file);
        $originalLines = explode("\n", $content);

        // insert replacements:
        $completeLines = $this->insertReplacements($originalLines, $replacements);


        // add indent
        $indentedLines = $this->indentLines($completeLines, $indentation);

        // add v1 or v2, if set.
        $pathInfoLines = $this->addApiVersion($indentedLines, $apiVersion);

        // replace version if necessary.

        $this->saveLines($apiVersion, $identifier, $pathInfoLines);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * @param string $server
     */
    public function setServer(string $server): void
    {
        $this->server = $server;
    }

    /**
     *
     * @throws RuntimeException
     */
    public function render(string $apiVersion): string
    {
        $this->logger->debug(sprintf('Rendering version "%s"', $apiVersion));
        try {
            $template = $this->twig->load('start.yaml.twig');
        } catch (SyntaxError|LoaderError|RuntimeError $e) {
            throw new RuntimeException(sprintf('Error in Twig: %s', $e->getMessage()));
        }
        // add tags
        $tags = '';
        $set  = $this->tags[$apiVersion] ?? [];
        if (count($set) > 0) {
            $array['tags'] = $set;
            $tags          = substr(substr(yaml_emit($array, YAML_UTF8_ENCODING), 4), 0, -4);
        }
        $time = Carbon::now('Europe/Amsterdam');

        $content = $template->render(
            [
                'version'     => $this->getVersion(),
                'server'      => $this->getServer(),
                'api_version' => $apiVersion,
                'tags'        => $tags,
                'paths'       => $this->paths[$apiVersion],
                'schemas'     => $this->schemas[$apiVersion],
                'time'        => $time->format('Y-m-d @ H:i:s (e)'),
            ]
        );
        $content = str_replace("\n\n", "\n", $content);
        $content = str_replace("\n  \n", "\n", $content);
        return str_replace("\n\n", "\n", $content);
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function parseReplacements(string $file): array
    {
        $replacements = [];
        $content      = (string)file_get_contents($file);
        $lines        = explode("\n", $content);
        // to make sure we wedge in the template at the right spot:

        // replace lines with (indented) templates.
        foreach ($lines as $i => $line) {
            $line = trim($line);
            if (str_starts_with($line, '!')) {
                $replacements[$i] = $this->getReplacementForExclamation($line);
            }
            if (str_starts_with($line, '_tpl_')) {
                $replacements[$i] = $this->getReplacementForTpl($line);
            }
        }

        return $replacements;
    }

    /**
     * @param string $instruction
     *
     * @return array
     */
    private function getReplacementForTpl(string $instruction): array
    {
        // _tpl_currencyFields,2:
        $instruction = substr($instruction, 5, -1);
        $parts       = explode(',', $instruction);
        $template    = $this->getReplacementTemplate($parts[0]);
//        $filename    = sprintf('%s/yaml/templates/%s.yaml', ROOT, $parts[0]);
//        $template    = file_get_contents($filename);
        $lines       = explode("\n", $template);
        $replacement = [];
        foreach ($lines as $line) {
            $indent        = str_repeat('  ', (int)($parts[1] ?? 0));
            $replacement[] = sprintf('%s%s', $indent, $line);
        }

        return $replacement;
    }


    /**
     * @param string $instruction
     *
     * @return array
     */
    private function getReplacementForExclamation(string $instruction): array
    {
        $parts    = explode(',', substr($instruction, 1));
        $template = $this->getReplacementTemplate($parts[0]);
//        $filename    = sprintf('%s/yaml/templates/%s.yaml', ROOT, $parts[0]);
//        $template    = file_get_contents($filename);
        $lines       = explode("\n", $template);
        $replacement = [];
        foreach ($lines as $line) {
            $indent        = str_repeat('  ', (int)($parts[1] ?? 0));
            $replacement[] = sprintf('%s%s', $indent, $line);
        }

        return $replacement;
    }

    /**
     * @param array $lines
     * @param array $replacements
     *
     * @return array
     */
    private function insertReplacements(array $lines, array $replacements): array
    {
        if (0 === count($replacements)) {
            return $lines;
        }
        $offset = 0;
        /**
         * @var int $i
         * @var array $replacement
         */
        foreach ($replacements as $i => $replacement) {
            $index = $i + $offset;

            // remove original index:
            array_splice($lines, $index, 1);

            // insert replacement into array:
            array_splice($lines, $index, 0, $replacement);
            $offset = $offset + count($replacement) - 1;
        }

        return $lines;
    }

    /**
     * @param array $lines
     * @param int $indentation
     *
     * @return array
     */
    private function indentLines(array $lines, int $indentation): array
    {
        $newLines = [];
        $indent   = '';
        if ($indentation > 0) {
            $indent = str_repeat('  ', $indentation);
        }
        foreach ($lines as $line) {
            $line       = $indent . rtrim($line);
            $newLines[] = $line;
        }

        return $newLines;
    }

    /**
     * @param string $identifier
     * @param array $lines
     *
     * @return void
     */
    private function saveLines(string $apiVersion, string $identifier, array $lines): void
    {
        $file = implode("\n", $lines);

        // replace version in final result:
        $file = $this->replacePlaceholderReference($file);

        if ($identifier === 'paths') {
            $this->paths[$apiVersion]   = $this->paths[$apiVersion] ?? [];
            $this->paths[$apiVersion][] = $file;
            return;
        }
        if ($identifier === 'schemas') {
            $this->schemas[$apiVersion]   = $this->schemas[$apiVersion] ?? [];
            $this->schemas[$apiVersion][] = $file;
            return;
        }

        throw new RuntimeException(sprintf('Invalid identifier "%s"', $identifier));
    }


    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param array $indentedLines
     * @param string $apiVersion
     *
     * @return array
     */
    private function addApiVersion(array $indentedLines, string $apiVersion): array
    {
        $newLines = [];
        foreach ($indentedLines as $line) {
            if (str_starts_with($line, '  /')) {
                $line = sprintf('  /%s%s', $apiVersion, trim($line));
                $this->logger->debug(sprintf('Added API version to line: %s', $line));
                //$this->logger->info(trim($line));
            }
            $newLines[] = $line;
        }

        return $newLines;
    }

    /**
     * @param array $versions
     */
    public function setApiVersions(array $versions): void
    {
        $this->logger->debug('API versions is now', $versions);
        $this->apiVersions = $versions;
    }

    private function getReplacementTemplate(string $file): string
    {
        if (0 === count($this->templates)) {
            $directory = sprintf('%s/yaml/templates', ROOT);
            $objects   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::SELF_FIRST);

            /** @var SplFileInfo $object */
            foreach ($objects as $object) {
                $fileName = $object->getFilename();
                if ('yaml' === $this->getExtension($fileName)) {
                    $this->templates[$fileName] = file_get_contents($object->getPathname());
                }
            }
        }
        $yaml = sprintf('%s.yaml', $file);
        if (isset($this->templates[$yaml])) {
            return $this->templates[$yaml];
        }

        echo PHP_EOL;
        echo 'Could not find replacement template for: ' . $file . PHP_EOL;
        echo PHP_EOL;
        exit;
    }

    private function getExtension(string $fileName)
    {
        $parts = explode('.', $fileName);
        if (count($parts) < 2) {
            return '';
        }
        return array_pop($parts);
    }

    private function replacePlaceholderReference(string $content): string
    {
        $placeholders    = ['%version%', '%start_date%', '%end_date%', '%start_date_and_time%','%end_date_and_time%'];
        $hasPlaceholders = false;
        foreach ($placeholders as $placeholder) {
            if (str_contains($content, $placeholder)) {
                $hasPlaceholders = true;
            }
        }

        if (!$hasPlaceholders) {
            return $content;
        }
        // replace version:
        $version = $this->version;
        if ('develop' === $this->version) {
            $version = '6.3.0';
        }
        if (str_starts_with($version, 'v')) {
            $version = substr($version, 1);
        }
        $this->logger->info(sprintf('Add version reference, "%s"', $version));
        $content = str_replace('%version%', $version, $content);

        // replace month start and end dates:
        $som = Carbon::now()->startOfMonth()->format('Y-m-d');
        $eom = Carbon::now()->endOfMonth()->format('Y-m-d');
        $content = str_replace('%start_date%', $som, $content);
        $content = str_replace('%end_date%', $eom, $content);
        unset($som, $eom);

        // replace month start and end date and time:
        $som = Carbon::now()->startOfMonth()->toAtomString();
        $eom = Carbon::now()->endOfMonth()->toAtomString();
        $content = str_replace('%start_date_and_time%', $som, $content);
        $content = str_replace('%end_date_and_time%', $eom, $content);
        unset($som, $eom);


        return $content;
    }

}
