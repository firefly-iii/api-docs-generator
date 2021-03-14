<?php
declare(strict_types=1);

namespace ApiDocBuilder\Builder;

use ApiDocBuilder\TwigExtension\Functions;
use Carbon\Carbon;
use Carbon\Factory;
use Twig\Environment;
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
    private string      $server;

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
        $this->server = '';

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
        $content = trim(file_get_contents($file));
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
        $time = Carbon::now();
        return $template->render(
            [
                'version' => $this->getVersion(),
                'server' => $this->getServer(),
                'tags'    => $tags,
                'paths'   => $this->paths,
                'schemas' => $this->schemas,
                'time' => $time->toW3cString()
            ]);
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