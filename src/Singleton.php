<?php

declare(strict_types=1);

namespace DOF\Util;

/**
 * Storage singleton object for 100% stateless, side-effect-free class
 */
final class Singleton
{
    private static $pool = [];

    public static function get(string $namespace, ...$params)
    {
        if ($instance = (self::$pool[$namespace] ?? null)) {
            return $instance;
        }

        if (! \class_exists($namespace)) {
            return null;
        }

        return self::$pool[$namespace] = new $namespace(...$params);
    }
}
