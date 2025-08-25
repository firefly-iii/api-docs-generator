<?php
/*
 * Builder.php
 * Copyright (c) 2025 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace ApiDocBuilder\Builder;

use Carbon\Carbon;
use Monolog\Logger;
use RuntimeException;
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
    private array       $paths     = [];
    private array       $schemas   = [];
    private array       $tags      = [];
    private Environment $twig;
    private string      $version   = '1.0';
    private string      $server    = '';
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
        $loader     = new FilesystemLoader($templatePath);
        $this->twig = new Environment($loader, ['cache' => $cachePath, 'charset' => 'utf-8', 'auto_reload' => true]);
    }

    /**
     * @param string $name
     * @param array $info
     */
    public function addTag(string $name, array $info): void
    {
        $this->tags[] = [
            'name'        => $name,
            'description' => $info['description'],
        ];
        $this->logger->debug(sprintf('Added tag "%s".', $name));
    }

    /**
     * @param string $apiVersion
     * @param string $identifier
     * @param string $file
     * @param int $indentation
     *
     */
    public function addYamlFile(string $identifier, string $file, int $indentation): void
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
        $pathInfoLines = $this->addApiVersion($indentedLines);

        // replace version if necessary.

        $this->saveLines($identifier, $pathInfoLines);
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
    public function render(): string
    {
        $this->logger->debug('Rendering API documentation');
        try {
            $template = $this->twig->load('start.yaml.twig');
        } catch (SyntaxError|LoaderError|RuntimeError $e) {
            throw new RuntimeException(sprintf('Error in Twig: %s', $e->getMessage()));
        }
        // add tags
        $tags = '';
        if (count($this->tags) > 0) {
            $array['tags'] = $this->tags;
            $tags          = substr(substr(yaml_emit($array, YAML_UTF8_ENCODING), 4), 0, -4);
        }
        $time = Carbon::now('Europe/Amsterdam');

        $content = $template->render(
            [
                'version' => $this->getVersion(),
                'server'  => $this->getServer(),
                'tags'    => $tags,
                'paths'   => $this->paths,
                'schemas' => $this->schemas,
                'time'    => $time->format('Y-m-d @ H:i:s (e)'),
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
        if ('' === $content) {
            $this->logger->error(sprintf('Unable to read file "%s".', $file));
            exit(1);
        }
        $lines = explode("\n", $content);
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
        $instruction = substr($instruction, 5, -1);
        $parts       = explode(',', $instruction);
        $template    = $this->getReplacementTemplate($parts[0]);
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
    private function saveLines(string $identifier, array $lines): void
    {
        $file = implode("\n", $lines);

        // replace version in final result:
        $file = $this->replacePlaceholderReference($file);

        if ($identifier === 'paths') {
            $this->paths[] = $file;
            return;
        }
        if ($identifier === 'schemas') {
            $this->schemas[] = $file;
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
    private function addApiVersion(array $indentedLines): array
    {
        $newLines = [];
        foreach ($indentedLines as $line) {
            if (str_starts_with($line, '  /')) {
                $line = sprintf('  /v1%s', trim($line));
                $this->logger->debug(sprintf('Added API version to line: %s', $line));
            }
            $newLines[] = $line;
        }

        return $newLines;
    }


    private function getReplacementTemplate(string $file): string
    {
        if (0 === count($this->templates)) {
            $directory = sprintf('%s/templates', ROOT);

            // check each file in the directory and see if it needs action.
            // collect recursively:
            $it    = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS));
            $Regex = new \RegexIterator($it, '/^.+\.yaml$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach ($Regex as $item) {
                $path                   = $item[0];
                $this->templates[basename($path)] = file_get_contents($path);
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
        $placeholders    = ['%version%', '%start_date%', '%end_date%', '%start_date_and_time%', '%end_date_and_time%'];
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
        $som     = Carbon::now()->startOfMonth()->format('Y-m-d');
        $eom     = Carbon::now()->endOfMonth()->format('Y-m-d');
        $content = str_replace('%start_date%', $som, $content);
        $content = str_replace('%end_date%', $eom, $content);
        unset($som, $eom);

        // replace month start and end date and time:
        $som     = Carbon::now()->startOfMonth()->toAtomString();
        $eom     = Carbon::now()->endOfMonth()->toAtomString();
        $content = str_replace('%start_date_and_time%', $som, $content);
        $content = str_replace('%end_date_and_time%', $eom, $content);
        unset($som, $eom);


        return $content;
    }

}
