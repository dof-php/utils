<?php

declare(strict_types=1);

namespace DOF\Util;

use DOF\Util\Exceptor\TypeHintExceptor;

class TypeHint
{
    /**
     * type-hint a value to a given type
     *
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    public static function typehint(string $type, $value = null, ...$extra) : bool
    {
        $hint = \strtolower(\trim($type));

        if (IS::empty($hint)) {
            return false;
        }

        if (static::support($hint)) {
            return static::{$hint}($value, ...$extra);
        }

        throw new TypeHintExceptor('UNTYPEHINTABLE_VALUE', \compact('type', 'value'));
    }

    public static function support(string $type) : bool
    {
        return \method_exists(static::class, \strtolower(\trim($type)));
    }

    public static function bool($value) : bool
    {
        return \is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1';
    }

    public static function boolean($value) : bool
    {
        return static::bool($value);
    }

    public static function int($value) : bool
    {
        if (! \is_numeric($value)) {
            return false;
        }

        $_val = \intval($value);

        return $value == $_val;
    }

    public static function integer($value) : bool
    {
        return static::int($value);
    }

    public static function string($value) : bool
    {
        if (\is_bool($value) || \is_null($value)) {
            return false;
        }

        if (\is_scalar($value)) {
            return true;
        }

        return (true
            && \is_object($value)
            && \method_exists($value, '__toString')
            && \is_string($value->__toString())
        );
    }

    public static function float($value) : bool
    {
        if (! \is_numeric($value)) {
            return false;
        }

        $_val = \floatval($value);

        return $value == $_val;
    }

    public static function double($value) : bool
    {
        return static::float($value);
    }

    public static function array($value) : bool
    {
        if (\is_array($value)) {
            return true;
        }

        return (true
            && \is_object($value)
            && \method_exists($value, '__toArray')
            && \is_array($value->__toArray())
        );
    }

    public static function bint($value) : bool
    {
        return ($value === 0) || ($value === 1) || $value === '0' || $value === '1';
    }

    public static function uint($value) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        return $value >= 0;
    }

    public static function pint($value) : bool
    {
        if (! static::uint($value)) {
            return false;
        }

        return $value > 0;
    }

    public static function nint($value) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        return $value < 0;
    }

    public static function list($value) : bool
    {
        return IS::array($value, 'index');
    }

    public static function namespace($value) : bool
    {
        return IS::namespace($value);
    }

    public static function char($value) : bool
    {
        return static::string($value);
    }

    public static function tinytext($value) : bool
    {
        return static::string($value);
    }

    public static function mediumtext($value) : bool
    {
        return static::string($value);
    }

    public static function longtext($value) : bool
    {
        return static::string($value);
    }

    public static function text($value) : bool
    {
        return static::string($value);
    }

    public static function varchar($value) : bool
    {
        return static::string($value);
    }

    public static function bigint($value, bool $unsigned = false) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        $max = (int) number_format(pow(2, 63), 0, '', '');

        return $unsigned
            ? (($value >= 0) && ($value <= ($max - 1)))
            : (($value >= -$max) && ($value <= ($max - 1)));
    }

    public static function mediumint($value, bool $unsigned = false) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        return $unsigned
            ? (($value >= 0) && ($value <= 16777215))
            : (($value >= -8388608) && ($value <= 8388607));
    }

    public static function smallint($value, bool $unsigned = false) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        return $unsigned
            ? (($value >= 0) && ($value <= 65535))
            : (($value >= -32768) && ($value <= 32767));
    }


    public static function tinyint($value, bool $unsigned = false) : bool
    {
        if (! static::int($value)) {
            return false;
        }

        return $unsigned
            ? (($value >= 0) && ($value <= 255))
            : (($value >= -128) && ($value <= 127));
    }

    public static function decimal($value) : bool
    {
        return static::float($value);
    }
}
