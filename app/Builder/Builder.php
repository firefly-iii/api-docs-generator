<?php
declare(strict_types=1);

namespace ApiDocBuilder\Builder;

use ApiDocBuilder\TwigExtension\Functions;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Builder
{
    /** @var array List of YAML strings to be pasted under the "paths" tag. */
    private $paths;
    /** @var array */
    private $schemas;
    /** @var array */
    private $tags;
    /** @var Twig_Environment */
    private $twig;
    /** @var string */
    private $version;

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
        $loader        = new Twig_Loader_Filesystem($templatePath);
        $this->twig    = new Twig_Environment($loader, ['cache' => $cachePath, 'charset' => 'utf-8', 'auto_reload' => true]);
    }

    /**
     * @param string $name
     * @param string $description
     */
    public function addTag(string $name, string $description): void
    {
        $this->tags[] = [
            'name'        => $name,
            'description' => $description,
        ];
    }

    /**
     * @param string $identifier
     * @param string $file
     * @param int    $indentation
     *
     * @throws \RuntimeException
     */
    public function addYamlFile(string $identifier, string $file, int $indentation): void
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('No such file: %s', $file));
        }
        $content = file_get_contents($file);
        $lines   = explode("\n", $content);

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

        $newYaml = implode("\n", $newLines);
        if (isset($this->$identifier)) {
            $this->$identifier[] = $newYaml;
        }
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
     *
     * @throws \RuntimeException
     */
    public function render(): string
    {
        try {
            $template = $this->twig->load('start.yaml.twig');
        } catch (\Twig_Error_Loader $e) {
        } catch (\Twig_Error_Runtime $e) {
        } catch (\Twig_Error_Syntax $e) {
            throw new \RuntimeException(sprintf('Error in Twig: %s', $e->getMessage()));
        }
        // add tags
        $tags = '';
        if (\count($this->tags) > 0) {
            $array['tags'] = $this->tags;
            $tags          = substr(substr(yaml_emit($array, YAML_UTF8_ENCODING), 4), 0, -4);
        }

        return $template->render(['version' => $this->getVersion(), 'tags' => $tags, 'paths' => $this->paths, 'schemas' => $this->schemas]);
    }

    /**
     * @param string $identifier
     * @param string $file
     * @param int    $indentation
     *
     * @throws \RuntimeException
     */
    public function renderYamlFile(string $identifier, string $file, int $indentation): void
    {
        try {
            $template = $this->twig->load('paths/' . $file);
        } catch (\Twig_Error_Loader $e) {
        } catch (\Twig_Error_Runtime $e) {
        } catch (\Twig_Error_Syntax $e) {
            throw new \RuntimeException(sprintf('Error in Twig: %s', $e->getMessage()));
        }
        $render = $template->render();
        $lines  = explode("\n", $render);
        // add indentation:
        $indent = '';
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
        $newRender = implode("\n", $newLines);


        if (isset($this->$identifier)) {
            $this->$identifier[] = $newRender;
        }
    }

}