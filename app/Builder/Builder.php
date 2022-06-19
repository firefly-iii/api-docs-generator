<?php
declare(strict_types=1);

namespace ApiDocBuilder\Builder;

use Carbon\Carbon;
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
    private array $paths;
    private array $schemas;
    private array $tags;
    private Environment $twig;
    private string $version;
    private string $server;

    /**
     * Builder constructor.
     *
     * @param string $templatePath
     * @param string $cachePath
     */
    public function __construct(string $templatePath, string $cachePath)
    {
        $this->tags    = [];
        $this->paths   = [];
        $this->schemas = [];
        $this->server  = '';

        $loader     = new FilesystemLoader($templatePath);
        $this->twig = new Environment($loader, ['cache' => $cachePath, 'charset' => 'utf-8', 'auto_reload' => true]);
    }

    /**
     * @param string $name
     * @param string $description
     */
    public function addTag(string $name, string $description): void
    {
        $this->tags[] = [
            'name' => $name,
            'description' => $description,
        ];
    }

    /**
     * @param string $identifier
     * @param string $file
     * @param int $indentation
     *
     * @throws \RuntimeException
     */
    public function addYamlFile(string $identifier, string $file, int $indentation): void
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('No such file: %s', $file));
        }
        $content = trim((string)file_get_contents($file));
        if ('' === $content) {
            return;
        }
        // parse replacements:
        $replacements = $this->parseReplacements($file);
        $originalLines = explode("\n", $content);

        // insert replacements:
        $completeLines = $this->insertReplacements($originalLines, $replacements);

        // add indent
        $indentedLines = $this->indentLines($completeLines, $indentation);

        $this->saveLines($identifier, $indentedLines);
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
     * @throws \RuntimeException
     */
    public function render(): string
    {
        try {
            $template = $this->twig->load('start.yaml.twig');
        } catch (SyntaxError|LoaderError|RuntimeError $e) {
            throw new \RuntimeException(sprintf('Error in Twig: %s', $e->getMessage()));
        }
        // add tags
        $tags = '';
        if (\count($this->tags) > 0) {
            $array['tags'] = $this->tags;
            $tags          = substr(substr(yaml_emit($array, YAML_UTF8_ENCODING), 4), 0, -4);
        }
        $time = Carbon::now();
        return $template->render(
            [
                'version' => $this->getVersion(),
                'server' => $this->getServer(),
                'tags' => $tags,
                'paths' => $this->paths,
                'schemas' => $this->schemas,
                'time' => $time->toW3cString(),
            ]);
    }

    /**
     * @param string $file
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
                $replacements[$i] = $this->getReplacement($line);
            }
        }


        return $replacements;
    }

    /**
     * @param string $instruction
     * @return array
     */
    private function getReplacement(string $instruction): array
    {
        $parts       = explode(',', substr($instruction, 1));
        $filename    = sprintf('%s/yaml/%s/%s.yaml', ROOT, 'v2/templates', $parts[0]);
        $template    = file_get_contents($filename);
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
     * @return array
     */
    private function insertReplacements(array $lines, array $replacements): array
    {
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
            $offset = count($replacement) - 1;
        }
        return $lines;
    }

    /**
     * @param array $lines
     * @param int $indentation
     * @return array
     */
    private function indentLines(array $lines, int $indentation): array
    {
        $newLines = [];
        $indent   = '';
        if (0 === $indentation) {
            $indent = '';
        }
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
     * @return void
     */
    private function saveLines(string $identifier, array $lines): void
    {
        $file = implode("\n", $lines);
        if (isset($this->$identifier)) {
            $this->$identifier[] = $file;
            return;
        }
        throw new \RuntimeException(sprintf('Invalid identifier %s', $identifier));
    }
}