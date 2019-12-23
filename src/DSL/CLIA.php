<?php

declare(strict_types=1);

namespace DOF\Util\DSL;

/**
 * Command line interface arguments parser
 */
final class CLIA
{
    public static function build(array $argvs) : array
    {
        list($options, $params) = CLIA::parse($argvs);

        $entry = $params[0] ?? null;
        $cmd   = $params[1] ?? null;

        unset($params[0], $params[1]);

        return [$entry, $cmd, $options, \array_values($params)];
    }

    public static function parse(array $argvs) : array
    {
        $options = $params = [];
        $paramSeparator = false;
        foreach ($argvs as $idx => $arg) {
            if (! \is_string($arg)) {
                continue;
            }
            if ($arg === '--') {
                $paramSeparator = $idx;
                break;
            }
            if (\mb_strcut($arg, 0, 2) === '--') {
                $option = CLIA::option($arg);
                if ($option) {
                    list($name, $_params) = $option;
                    $options[$name] = $_params;
                }
                continue;
            }

            $params[] = $arg;
        }

        if (false !== $paramSeparator) {
            for ($i = 0; $i <= $paramSeparator; $i++) {
                unset($argvs[$i]);
            }

            $params = \array_merge($params, $argvs);
        }

        return [$options, $params];
    }

    public static function option(string $option) : ?array
    {
        $option = \mb_strcut($option, 2);
        if (! $option) {
            return null;
        }

        $sidx = \mb_strpos($option, '=');     // separator index
        if (false === $sidx) {
            // Here 2nd value must be null if separator index not exists
            // To avoid creation of empty colloction object
            return [$option, null];
        }

        $name    = \mb_substr($option, 0, $sidx);
        $_params = \mb_strcut($option, $sidx+1);
        $params  = [];
        \parse_str($_params, $params);
        if ((\count($params) === 1) && (\array_values($params)[0] === '')) {
            $params = $_params;
        }

        return [$name, $params];
    }

    public static function compile(string $cli) : array
    {
        return CLIA::build(\array_filter(\explode(' ', $cli)));
    }
}
