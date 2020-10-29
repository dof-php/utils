<?php

declare(strict_types=1);

namespace DOF\Util;

use Throwable;

use DOF\Util\Exceptor;

/**
 * Proxy for class with empty-parameter constructor
 */
abstract class Surrogate
{
    private static $__POOL__;

    final public static function __callStatic(string $method, array $params = [])
    {
        return \call_user_func_array([self::instance(), $method], $params);
    }

    final public static function instance($instance = null)
    {
        $namespace = static::namespace();

        if ($instance && ($instance instanceof $namespace)) {
            return self::$__POOL__[$namespace] = $instance;
        }

        if (! static::singleton()) {
            return static::new();
        }

        if ($instance = (self::$__POOL__[$namespace] ?? null)) {
            return $instance;
        }

        return self::$__POOL__[$namespace] = static::new();
    }

    abstract public static function namespace() : string;

    public static function singleton() : bool
    {
        return false;
    }

    public static function new()
    {
        if (\class_exists($proxy = static::namespace())) {
            return new $proxy;
        }

        throw new Exceptor('INVALID_PROXY_NAMESPACE', \compact('proxy'));
    }
}
