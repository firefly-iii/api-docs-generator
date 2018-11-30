<?php
declare(strict_types=1);

namespace ApiDocBuilder\TwigExtension;


use Twig_Function;

/**
 * Class Functions
 */
class Functions extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new Twig_Function('includeDefaultParameter', [$this, 'includeDefaultParameter']),
        ];
    }

    /**
     * @param string $name
     * @param int    $indentation
     *
     * @return string
     * @throws \RuntimeException
     */
    public function includeDefaultParameter(string $name, int $indentation): string
    {
        $yaml = '';
        switch ($name) {
            default:
                throw new \RuntimeException(sprintf('Cannot handle "%s"', $name));
            case 'id':
            case 'page':
            case 'start_date':
            case 'transaction_type':
            case 'end_date':
            case 'budgetID':
            case 'budgetLimitID':
                $file = sprintf('%s/templates/parameters/%s.yaml', ROOT, $name);
                if (!file_exists($file)) {
                    throw new \RuntimeException(sprintf('No such file: %s', $file));
                }
                $yaml = file_get_contents($file);
                break;
        }
        $lines    = explode("\n", $yaml);
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

        return implode("\n", $newLines);
    }

}
