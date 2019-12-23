<?php

declare(strict_types=1);

namespace DOF\Util;

final class F
{
    public static function unsplat($params = null)
    {
        return $params;
    }

    public static function phpuser(string $default = 'ghost') : string
    {
        if (\extension_loaded('posix')) {
            return \posix_getpwuid(\posix_geteuid())['name'] ?? $default;
        }

        return \get_current_user() ?: $default;
    }

    public static function nanoseconds() : int
    {
        return \intval(\microtime(true) * 1000000000);
    }

    public static function microseconds() : int
    {
        return \intval(\microtime(true) * 1000000);
    }

    public static function milliseconds() : int
    {
        return \intval(\microtime(true) * 1000);
    }
}
